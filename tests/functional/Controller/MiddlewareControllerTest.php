<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Explorer\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Module\Explorer\Controller\MiddlewareController;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\Media\PositionRepository;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use GibsonOS\Module\Explorer\Store\Html5\ToSeeStore;
use GibsonOS\Test\Functional\Explorer\ExplorerFunctionalTest;
use OutOfRangeException;
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

        $this->checkSuccessResponse(
            $this->middlewareController->toSeeList($this->serviceManager->get(ToSeeStore::class), $userIds),
            $responseData,
            $total
        );
    }

    public function testSavePosition(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $media = (new Media())
            ->setToken('galaxy')
            ->setDir('')
            ->setFilename('ford')
            ->setUser($this->addUser())
            ->setStatus('generated')
            ->setAdded(new DateTimeImmutable())
        ;
        $modelManager->saveWithoutChildren($media);

        $this->checkSuccessResponse($this->middlewareController->savePosition(
            $this->serviceManager->get(MediaService::class),
            $media,
            42,
            [1],
        ));

        $position = $this->serviceManager->get(PositionRepository::class)->getByMediaAndUserId(
            $media->getId(),
            1,
        );
        $this->assertEquals(42, $position->getPosition());

        $this->checkSuccessResponse($this->middlewareController->savePosition(
            $this->serviceManager->get(MediaService::class),
            $media,
            24,
            [1],
        ));

        $position = $this->serviceManager->get(PositionRepository::class)->getByMediaAndUserId(
            $media->getId(),
            1,
        );
        $this->assertEquals(24, $position->getPosition());

        $this->expectException(OutOfRangeException::class);
        $this->middlewareController->savePosition(
            $this->serviceManager->get(MediaService::class),
            $media,
            0,
            [1],
        );
    }

    public function testImage(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $media = (new Media())
            ->setToken('galaxy')
            ->setDir('')
            ->setFilename('ford')
            ->setUser($this->addUser())
            ->setStatus('generated')
            ->setAdded(new DateTimeImmutable())
        ;
        $modelManager->saveWithoutChildren($media);

        $this->gibsonStoreService->hasFileImage('ford')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $image = $this->prophesize(Image::class);
        $this->gibsonStoreService->getFileImage('ford', 42, 42)
            ->shouldBeCalledOnce()
            ->willReturn($image->reveal())
        ;
        $imageService = $this->prophesize(ImageService::class);
        $imageService->getString($image->reveal())
            ->shouldBeCalledOnce()
            ->willReturn('prefect')
        ;

        $response = $this->middlewareController->image(
            $this->gibsonStoreService->reveal(),
            $imageService->reveal(),
            $this->serviceManager->get(TypeFactory::class),
            $media,
            42,
            42,
        );

        $this->assertEquals('prefect', $response->getBody());
        $this->assertEquals(
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => 7,
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ],
            $response->getHeaders()
        );
    }

//    public function testNewImage(): void
//    {
//        $modelManager = $this->serviceManager->get(ModelManager::class);
//        $media = (new Media())
//            ->setToken('galaxy')
//            ->setDir('')
//            ->setFilename('ford')
//            ->setUser($this->addUser())
//            ->setStatus('generated')
//            ->setAdded(new DateTimeImmutable())
//        ;
//        $modelManager->saveWithoutChildren($media);
//
//        $this->gibsonStoreService->hasFileImage('ford')
//            ->shouldBeCalledOnce()
//            ->willReturn(false)
//        ;
//        $image = $this->prophesize(Image::class);
//        $this->gibsonStoreService->getFileImage('ford', 42, 42)
//            ->shouldBeCalledOnce()
//            ->willReturn($image->reveal())
//        ;
//        $imageService = $this->prophesize(ImageService::class);
//        $imageService->getString($image->reveal())
//            ->shouldBeCalledOnce()
//            ->willReturn('prefect')
//        ;
//
//        $response = $this->middlewareController->image(
//            $this->gibsonStoreService->reveal(),
//            $imageService->reveal(),
//            $this->serviceManager->get(TypeFactory::class),
//            $media,
//            42,
//            42,
//        );
//
//        $this->assertEquals('prefect', $response->getBody());
//        $this->assertEquals(
//            [
//                'Pragma' => 'public',
//                'Expires' => 0,
//                'Accept-Ranges' => 'bytes',
//                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
//                'Content-Type' => 'image/jpg',
//                'Content-Length' => 7,
//                'Content-Transfer-Encoding' => 'binary',
//                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
//            ],
//            $response->getHeaders()
//        );
//    }
}
