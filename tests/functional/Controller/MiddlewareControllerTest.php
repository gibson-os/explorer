<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Explorer\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Module\Explorer\Controller\MiddlewareController;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Store\Html5\ToSeeStore;
use GibsonOS\Test\Functional\Explorer\ExplorerFunctionalTest;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class MiddlewareControllerTest extends ExplorerFunctionalTest
{
    use ProphecyTrait;
    use MiddlewareControllerToSeeDataTrait;

    private MiddlewareController $middlewareController;

    private GibsonStoreService|ObjectProphecy $gibsonStoreService;

    protected function _before(): void
    {
        parent::_before();

        $this->gibsonStoreService = $this->prophesize(GibsonStoreService::class);
        $this->serviceManager->setService(GibsonStoreService::class, $this->gibsonStoreService->reveal());

        $this->middlewareController = $this->serviceManager->get(MiddlewareController::class);
    }

    /**
     * @dataProvider getToSeeData
     */
    public function testToSeeList(array $medias, array $userIds, array $responseData, int $total): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);

        $this->addUser();
        $this->addUser('arthur');

        foreach ($medias as $index => $media) {
            $dir =
                __DIR__ . DIRECTORY_SEPARATOR .
                '..' . DIRECTORY_SEPARATOR .
                '..' . DIRECTORY_SEPARATOR .
                '_data' . DIRECTORY_SEPARATOR .
                'media' . DIRECTORY_SEPARATOR . ($media['dir'] ?? '')
            ;
            $mediaModel = (new Media())
                ->setToken($media['filename'])
                ->setDir($dir)
                ->setFilename($media['filename'])
                ->setUserId($media['userId'] ?? 1)
                ->setStatus($media['status'] ?? 'generated')
                ->setAdded(new DateTimeImmutable('+' . $index . ' seconds'))
            ;
            $modelManager->saveWithoutChildren($mediaModel);
            $callTimes = 0;

            if (isset($media['position'])) {
                $modelManager->saveWithoutChildren(
                    (new Media\Position())
                        ->setUserId(1)
                        ->setPosition($media['position'])
                        ->setMedia($mediaModel)
                        ->setModified(new DateTimeImmutable('+' . $index . '11 seconds'))
                );

                if (in_array(1, $userIds)) {
                    $callTimes += 2;
                }
            }

            if (isset($media['position2'])) {
                $modelManager->saveWithoutChildren(
                    (new Media\Position())
                        ->setUserId(2)
                        ->setPosition($media['position2'])
                        ->setMedia($mediaModel)
                        ->setModified(new DateTimeImmutable('+' . $index . '22 seconds'))
                );

                if (in_array(2, $userIds)) {
                    $callTimes += 2;
                }
            }

            $this->gibsonStoreService->getFileMeta($dir . $media['filename'], 'duration', 0)
                ->shouldBeCalledTimes($callTimes === 0 ? 1 : $callTimes)
                ->willReturn($media['duration'] ?? 42)
            ;
        }

        $response = $this->middlewareController->toSeeList($this->serviceManager->get(ToSeeStore::class), $userIds);
        $this->checkAjaxResponse($response, $responseData, $total);
    }
}
