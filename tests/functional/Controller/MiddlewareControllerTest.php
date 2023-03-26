<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Explorer\Controller;

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

        $marvin = $this->addUser();
        $arthur = $this->addUser('arthur');

        foreach ($medias as $media) {
            $modelManager->saveWithoutChildren(
                (new Media())
                    ->setToken($media['filename'])
                    ->setDir($media['dir'] ?? '')
                    ->setFilename($media['filename'])
                    ->setUserId($media['userId'] ?? 1)
                    ->setStatus($media['status'] ?? 'generated')
            );
            $this->gibsonStoreService->getFileMeta(($media['dir'] ?? '') . $media['filename'], 'duration', 0)
                ->shouldBeCalledOnce()
                ->willReturn($media['duration'] ?? 42)
            ;
        }

        $response = $this->middlewareController->toSeeList($this->serviceManager->get(ToSeeStore::class), $userIds);
        $this->checkAjaxResponse($response, $responseData, $total);
    }

    public function getToSeeData(): array
    {
        return [
            'empty' => [
                [],
                [1],
                [],
                0,
            ],
            'one media' => [
                [
                    ['filename' => 'ford'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford',
                        'html5MediaToken' => 'ford',
                        'dir' => '',
                        'filename' => 'ford',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                1,
            ],
            'two medias same path' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => '',
                        'filename' => 'ford 1e2',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
        ];
    }
}
