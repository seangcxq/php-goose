<?php declare(strict_types = 1);

namespace Goose\Images;

use Goose\Configuration;
use GuzzleHttp\
{Client, Pool};

use stdClass;

/**
 * Image Utils
 *
 * @package Goose\Images
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */
class ImageUtils
{
	/**
	 * @param string $filePath
	 *
	 * @return object|null
	 */
	public static function getImageDimensions(string $filePath): ?stdClass
	{
		[$width, $height, $type] = getimagesize($filePath);

		if($type === NULL)
		{
			return NULL;
		}

		return (object)[
			'width' => (int)$width,
			'height' => (int)$height,
			'mime' => image_type_to_mime_type($type),
		];
	}

	/**
	 * Writes an image src http string to disk as a temporary file and returns the LocallyStoredImage object that has the info you should need
	 * on the image
	 *
	 * @param string[] $imageSrcs
	 * @param bool $returnAll
	 * @param Configuration $config
	 *
	 * @return LocallyStoredImage[]
	 */
	public static function storeImagesToLocalFile($imageSrcs, bool $returnAll, Configuration $config): array
	{
		$localImages = self::handleEntity($imageSrcs, $returnAll, $config);

		if(empty($localImages))
		{
			return [];
		}

		$locallyStoredImages = [];

		foreach($localImages as $localImage)
		{
			if(empty($localImage->file) || !filesize($localImage->file))
			{
				continue;
			}

			$imageDetails = self::getImageDimensions($localImage->file);

			if($imageDetails !== NULL)
			{
				$locallyStoredImages[] = new LocallyStoredImage([
					'imgSrc' => $localImage->url,
					'localFileName' => $localImage->file,
					'bytes' => filesize($localImage->file),
					'height' => $imageDetails->height,
					'width' => $imageDetails->width,
					'fileExtension' => self::getFileExtensionName($imageDetails),
				]);
			}
		}

		return $locallyStoredImages;
	}

	/**
	 * @param object $imageDetails
	 *
	 * @return string
	 */
	private static function getFileExtensionName(stdClass $imageDetails): string
	{
		$extensions = [
			'image/gif' => '.gif',
			'image/jpeg' => '.jpg',
			'image/png' => '.png',
		];

		return (
		isset($extensions[$imageDetails->mime])
			? $extensions[$imageDetails->mime]
			: 'NA'
		);
	}

	private static function handleEntity($imageSrcs, bool $returnAll, Configuration $config): ?array
	{
		return NULL;
	}
}
