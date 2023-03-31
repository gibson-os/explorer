<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Explorer\Controller;

trait MiddlewareControllerToSeeDataTrait
{
    public function getToSeeData(): array
    {
        $dir =
            __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '_data' . DIRECTORY_SEPARATOR .
            'media' . DIRECTORY_SEPARATOR
        ;

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
                        'dir' => $dir,
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
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path other order' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path first seen other order' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path first half seen' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first half seen other order' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first 90 percent seen' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 38],
                    ['filename' => 'ford 1e2'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path first 90 percent seen other order' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 38],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path second seen' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen other order' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second half seen' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second half seen other order' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second 90 percent seen' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 38],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second 90 percent seen other order' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 38],
                    ['filename' => 'ford 1e1'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen first half seen' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen first half seen other order' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen second half seen' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
                        'filename' => 'ford 1e2',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen second half seen other order' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
                        'filename' => 'ford 1e2',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path different names' => [
                [
                    ['filename' => 'prefect 1e2'],
                    ['filename' => 'ford 1e1'],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ], [
                        'html5VideoToken' => 'prefect 1e2',
                        'html5MediaToken' => 'prefect 1e2',
                        'dir' => $dir,
                        'filename' => 'prefect 1e2',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both seen' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [1],
                [],
                2,
            ],
            'two medias same path both seen other order' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [1],
                [],
                2,
            ],
            'two medias same path both half seen' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both half seen other order' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias different path same names' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'dir' => 'prefect' . DIRECTORY_SEPARATOR],
                ],
                [1],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir . 'prefect' . DIRECTORY_SEPARATOR,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ], [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path first seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2'],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path first seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path first half seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2'],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first half seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first 90 percent seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 38],
                    ['filename' => 'ford 1e2'],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path first 90 percent seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 38],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
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
            'two medias same path second seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1'],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second half seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second half seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1'],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second 90 percent seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 38],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second 90 percent seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 38],
                    ['filename' => 'ford 1e1'],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen first half seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen first half seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen second half seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
                        'filename' => 'ford 1e2',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen second half seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e2',
                        'html5MediaToken' => 'ford 1e2',
                        'dir' => $dir,
                        'filename' => 'ford 1e2',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 0,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [1, 2],
                [],
                2,
            ],
            'two medias same path both seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [1, 2],
                [],
                2,
            ],
            'two medias same path both half seen. 2 users connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both half seen other order. 2 users connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 21,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2'],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first half seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2'],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first half seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first 90 percent seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 38],
                    ['filename' => 'ford 1e2'],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first 90 percent seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2'],
                    ['filename' => 'ford 1e1', 'position' => 38],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1'],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second half seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second half seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1'],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second 90 percent seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1'],
                    ['filename' => 'ford 1e2', 'position' => 38],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second 90 percent seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 38],
                    ['filename' => 'ford 1e1'],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen first half seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path second seen first half seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen second half seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path first seen second half seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 42],
                    ['filename' => 'ford 1e2', 'position' => 42],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 42],
                    ['filename' => 'ford 1e1', 'position' => 42],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both half seen. other user connected' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 21],
                    ['filename' => 'ford 1e2', 'position' => 21],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'two medias same path both half seen other order. other user connected' => [
                [
                    ['filename' => 'ford 1e2', 'position' => 21],
                    ['filename' => 'ford 1e1', 'position' => 21],
                ],
                [2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 0,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'one media first user have seen more' => [
                [
                    ['filename' => 'ford 1e1', 'position' => 30, 'position2' => 20],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 30,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
            'one media second user have seen more' => [
                [
                    ['filename' => 'ford 1e1', 'position2' => 30, 'position' => 20],
                ],
                [1, 2],
                [
                    [
                        'html5VideoToken' => 'ford 1e1',
                        'html5MediaToken' => 'ford 1e1',
                        'dir' => $dir,
                        'filename' => 'ford 1e1',
                        'status' => 'generated',
                        'duration' => 42,
                        'position' => 30,
                        'nextFiles' => 1,
                        'category' => 2,
                    ],
                ],
                2,
            ],
        ];
    }
}