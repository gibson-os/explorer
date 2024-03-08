<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Explorer\Model\Trash;
use GibsonOS\Module\Explorer\Repository\TrashRepository;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class TrashService
{
    public function __construct(
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly SettingRepository $settingRepository,
        private readonly TrashRepository $trashRepository,
        private readonly DateTimeService $dateTimeService,
        private readonly ModelManager $modelManager,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws SelectError
     * @throws SetError
     * @throws ClientException
     * @throws RecordException
     */
    public function add(string $dir, ?array $files = null, ?int $userId = null): array
    {
        $dir = $this->dirService->addEndSlash($dir);
        $trashDir = $this->dirService->addEndSlash(
            $this->settingRepository->getByKeyAndModuleName('explorer', $userId ?? 0, 'trashDir')->getValue(),
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
     *
     * @throws ClientException
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws ModelDeleteError
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     * @throws SetError
     */
    public function restore(array $tokens, ?int $userId): void
    {
        $trashDir = $this->dirService->addEndSlash(
            $this->settingRepository->getByKeyAndModuleName('explorer', $userId ?? 0, 'trashDir')->getValue(),
        );

        foreach ($this->trashRepository->getByTokens($tokens) as $trash) {
            $this->fileService->move(
                $trashDir . $trash->getToken(),
                $this->dirService->addEndSlash($trash->getDir()) . ($trash->getFilename() ?? ''),
            );
            $this->modelManager->delete($trash);
        }
    }

    /**
     * @throws ClientException
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws ModelDeleteError
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function delete(array $tokens, ?int $userId): void
    {
        $trashDir = $this->dirService->addEndSlash(
            $this->settingRepository->getByKeyAndModuleName('explorer', $userId ?? 0, 'trashDir')->getValue(),
        );

        foreach ($this->trashRepository->getByTokens($tokens) as $trash) {
            $this->fileService->delete($trashDir, $trash->getToken());
            $this->modelManager->delete($trash);
        }
    }

    /**
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws SaveError
     * @throws SetError
     * @throws ReflectionException
     */
    private function addElement(string $trashDir, string $dir, ?string $filename, ?int $userId): string
    {
        $isDir = is_dir($dir . $filename);
        $token = $this->trashRepository->getFreeToken();
        $trash = (new Trash($this->modelWrapper))
            ->setToken($token)
            ->setDir($dir . ($isDir ? $filename : ''))
            ->setFilename($isDir ? null : $filename)
            ->setAdded($this->dateTimeService->get())
            ->setUserId($userId)
        ;

        $this->fileService->move($dir . ($filename ?? ''), $trashDir . $token);
        $this->modelManager->save($trash);

        return $token;
    }
}
