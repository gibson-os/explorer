<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use Exception;
use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Enum\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * @description Convert queued medias
 */
#[Cronjob(user: 'root')]
#[Lock('explorerHtml5ConvertCommand')]
class ConvertCommand extends AbstractCommand
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly MediaService $mediaService,
        private readonly SettingRepository $settingRepository,
        private readonly ModelManager $modelManager,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ClientException
     * @throws RecordException
     */
    protected function run(): int
    {
        foreach ($this->mediaRepository->getAllByStatus(ConvertStatus::WAIT) as $media) {
            $this->modelManager->save($media->setStatus(ConvertStatus::GENERATE));

            try {
                $filename = $this->settingRepository->getByKeyAndModuleName(
                    'explorer',
                    0,
                    'html5_media_path',
                )->getValue() . $media->getToken() . '.';

                switch ($media->getType()) {
                    case FileTypeDescriberInterface::CATEGORY_VIDEO:
                        $this->mediaService->convertToMp4($media, $filename . 'mp4');

                        break;
                    case FileTypeDescriberInterface::CATEGORY_AUDIO:
                        $this->mediaService->convertToMp3($media, $filename . 'mp3');

                        break;
                }

                $media->setStatus(ConvertStatus::GENERATED);
            } catch (Exception $exception) {
                $media
                    ->setStatus(ConvertStatus::ERROR)
                    ->setMessage($exception->getMessage())
                ;

                if ($media->getSubtitleStream() !== Media::SUBTITLE_NONE) {
                    $media
                        ->setStatus(ConvertStatus::WAIT)
                        ->setMessage('Subtitle removed')
                        ->setSubtitleStream(Media::SUBTITLE_NONE)
                    ;
                }
            }

            $this->modelManager->save($media);

            // @todo c2dm muss noch rein
        }

        return self::SUCCESS;
    }
}
