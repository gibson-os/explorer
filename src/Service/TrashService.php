<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Explorer\Model\Trash;
use GibsonOS\Module\Explorer\Repository\TrashRepository;

class TrashService extends AbstractService
{
    public function __construct(
        private DirService $dirService,
        private FileService $fileService,
        private SettingRepository $settingRepository,
        private TrashRepository $trashRepository,
        private DateTimeService $dateTimeService
    ) {
    }

    /**
     * @throws CreateError
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws SaveError
     * @throws SetError
     * @throws SelectError
     */
    public function add(string $dir, array $files = null, int $userId = null): array
    {
        $dir = $this->dirService->addEndSlash($dir);
        $trashDir = $this->dirService->addEndSlash(
            $this->settingRepository->getByKeyAndModuleName('explorer', $userId ?? 0, 'trashDir')->getValue()
        );

        if (empty($files)) {
            return [$this->addElement($trashDir, $dir, null, $userId)];
        }

        $tokens = [];

        foreach ($files as $file) {
            $tokens[] = $this->addElement($trashDir, $dir, $file, $userId);
        }

        return $tokens;
    }

    /**
     * @param string[] $tokens
     * @throws CreateError
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws SelectError
     * @throws SetError
     * @throws ModelDeleteError
     */
    public function restore(array $tokens, ?int $userId): void
    {
        $trashDir = $this->dirService->addEndSlash(
            $this->settingRepository->getByKeyAndModuleName('explorer', $userId ?? 0, 'trashDir')->getValue()
        );

        foreach ($this->trashRepository->getByTokens($tokens) as $trash) {
            $this->fileService->move(
                $trashDir . $trash->getToken(),
                $this->dirService->addEndSlash($trash->getDir()) . ($trash->getFilename() ?? '')
            );
            $trash->delete();
        }
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws SetError
     */
    private function addElement(string $trashDir, string $dir, ?string $filename, ?int $userId): string
    {
        $token = $this->trashRepository->getFreeToken();
        $trash = (new Trash())
            ->setToken($token)
            ->setDir($dir)
            ->setFilename($filename)
            ->setAdded($this->dateTimeService->get())
            ->setUserId($userId)
        ;

        $this->fileService->move($dir . ($filename ?? ''), $trashDir . $token);
        $trash->save();

        return $token;
    }
}
