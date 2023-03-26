<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command;

use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Explorer\Model\Trash;
use GibsonOS\Module\Explorer\Repository\TrashRepository;
use JsonException;
use Psr\Log\LoggerInterface;

/**
 * @description Remove files in trash
 */
#[Cronjob(minutes: '0', seconds: '0')]
class TrashCommand extends AbstractCommand
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly TrashRepository $trashRepository,
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly ModelManager $modelManager,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws SelectError
     * @throws JsonException
     */
    protected function run(): int
    {
        $trashLifetime = (int) $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'trashLifetime')
            ->getValue()
        ;
        $trashDir = $this->dirService->addEndSlash(
            $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'trashDir')
                ->getValue()
        );

        $this->logger->info(sprintf('Delete files older than %d days.', $trashLifetime));

        foreach ($this->trashRepository->getOlderThanDays($trashLifetime) as $trash) {
            $this->deleteItem($trash, $trashDir);
        }

        return self::SUCCESS;
    }

    /**
     * @throws JsonException
     */
    private function deleteItem(Trash $trash, string $trashDir): void
    {
        $filename = $this->dirService->addEndSlash($trash->getDir()) . $trash->getFilename();
        $this->logger->info(sprintf('Delete %s', $filename));

        try {
            if ($trash->getFilename() === null) {
                $this->fileService->delete($trashDir . $trash->getToken());
            } else {
                $this->fileService->delete($trashDir, [$trash->getToken()]);
            }
        } catch (FileNotFound) {
            // Do nothing
        } catch (DeleteError|GetError) {
            $this->logger->warning(sprintf('File %s delete error!', $filename));

            return;
        }

        try {
            $this->modelManager->delete($trash);
        } catch (ModelDeleteError) {
            $this->logger->warning(sprintf('Record %s delete error!', $filename));
        }
    }
}
