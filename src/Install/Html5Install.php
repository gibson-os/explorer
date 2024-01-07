<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use ReflectionException;

class Html5Install extends AbstractInstall implements PriorityInterface
{
    /**
     * @throws CreateError
     * @throws InstallException
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    public function install(string $module): Generator
    {
        yield $html5MediaPathInput = $this->getSettingInput(
            'explorer',
            'html5_media_path',
            'What is the directory for HTML5 medias?',
        );
        $mediaPath = $this->dirService->addEndSlash($html5MediaPathInput->getValue() ?? '');

        if (!file_exists($mediaPath)) {
            $this->dirService->create($mediaPath);
        }

        $this->setSetting('explorer', 'html5_media_path', $mediaPath);

        yield $html5MediaSizeInput = $this->getSettingInput(
            'explorer',
            'html5_media_size',
            'How big should the folder for HTML5 media be? (Possible specifications: 1024, 1kb, 1mb, 1gb, 0 = infinite)',
        );
        $this->setSetting('explorer', 'html5_media_size', $this->checkSizeInput($html5MediaSizeInput));

        yield $html5MediaLifetimeInput = $this->getSettingInput(
            'explorer',
            'html5_media_lifetime',
            'How long should the HTML5 media be stored? (In days. 0 = infinite)',
        );
        $lifeTime = $html5MediaLifetimeInput->getValue() ?? '';

        if (!is_numeric($lifeTime)) {
            throw new InstallException(sprintf('"%s" is no numeric value!', $lifeTime));
        }

        $this->setSetting('explorer', 'html5_media_lifetime', $lifeTime);

        yield $html5MediaCountInput = $this->getSettingInput(
            'explorer',
            'html5_media_count',
            'How many HTML5 media should be created? (0 = infinite)',
        );
        $count = $html5MediaCountInput->getValue() ?? '';

        if (!is_numeric($count)) {
            throw new InstallException(sprintf('"%s" is no numeric value!', $lifeTime));
        }

        $this->setSetting('explorer', 'html5_media_count', $count);

        yield new Success('HTML5 settings set!');
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getModule(): string
    {
        return 'explorer';
    }

    public function getPriority(): int
    {
        return 500;
    }
}
