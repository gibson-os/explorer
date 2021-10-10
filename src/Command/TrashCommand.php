<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Explorer\Model\Trash;
use GibsonOS\Module\Explorer\Repository\TrashRepository;
use Psr\Log\LoggerInterface;

class TrashCommand extends AbstractCommand
{
    public function __construct(
        private SettingRepository $settingRepository,
        private TrashRepository $trashRepository,
        private DirService $dirService,
        private FileService $fileService,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    protected function run(): int
    {
        $trashLifetime = (int) $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'trashLifetime')
            ->getValue()
        ;
        $trashSize = (int) $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'trashSize')
            ->getValue()
        ;
        $trashDir = $this->dirService->addEndSlash(
            $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'trashDir')
                ->getValue()
        );

        foreach ($this->trashRepository->getOlderThanDays($trashLifetime) as $trash) {
            $this->deleteItem($trash, $trashDir);
        }

        return 0;
    }

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
            $trash->delete();
        } catch (ModelDeleteError) {
            $this->logger->warning(sprintf('Record %s delete error!', $filename));
        }
    }
}