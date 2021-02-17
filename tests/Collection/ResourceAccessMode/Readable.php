<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests\Collection\ResourceAccessMode;

use HNV\Http\Helper\Collection\CollectionInterface;
use HNV\Http\Stream\Collection\ResourceAccessMode\{
    Readable    as ResourceAccessModeReadable,
    NonSuitable as ResourceAccessModeNonSuitable
};

use function array_diff;
/** ***********************************************************************************************
 * Resource access mode readable values (for tests only) collection.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class Readable implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return array_diff(
            ResourceAccessModeReadable::get(),
            ResourceAccessModeNonSuitable::get()
        );
    }
}