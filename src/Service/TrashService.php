<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Explorer\Model\Trash;
use GibsonOS\Module\Explorer\Repository\TrashRepository;

class TrashService extends AbstractService
{
    private DirService $dirService;

    private FileService $fileService;

    private TrashRepository $trashRepository;

    private DateTimeService $dateTimeService;

    public function __construct(
        DirService $dirService,
        FileService $fileService,
        TrashRepository $trashRepository,
        DateTimeService $dateTimeService
    ) {
        $this->dirService = $dirService;
        $this->fileService = $fileService;
        $this->trashRepository = $trashRepository;
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     *
     * @return string[]
     */
    public function add(string $dir, array $files = null, int $userId = null, string $parentToken = null): array
    {
        $dir = $this->dirService->addEndSlash($dir);
        $token = $this->trashRepository->getFreeToken();

        $trash = (new Trash())
            ->setToken($token)
            ->setDir($dir)
            ->setAdded($this->dateTimeService->get())
            ->setUserId($userId)
        ;

        if (empty($files)) {
            $trash->save();

            return [$token];
        }

        $tokens = [];

//        foreach ($files as $filename) {
//            $filename = $this->fileService->getFilename($filename);
//
//            if (is_dir($dir . $filename)) {
//                $tokens[$filename] = $this->add($dir . $filename);
//            } else {
//                if (!file_exists($dir . $filename)) {
//                    throw new \Exception('Datei ' . $dir . $files . ' existiert nicht!');
//                }
//
//                $tokens[$filename] = $this->add($dir, $filename);
//            }
//        }

        // @todo fertig bauen

        return $tokens;
    }
}
