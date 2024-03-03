<?php 

namespace App\Service;

use Illuminate\Support\Facades\Storage;

class UploadImageService
{
	public static function fromBase64(string $base64Image, string $directory = 'images'): string
	{
		$data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
		$imageData = base64_decode($data);

		$mimeType = preg_replace('/^data:image\//', '', explode(';', $base64Image)[0]);
		$extension = self::getImageExtensionFromMimeType($mimeType);

		$imageName = uniqid() . '.' . $extension;
		$path = "public/{$directory}/" . $imageName;

		Storage::put($path, $imageData);

		return str_replace('public/', '', $path);
	}

	public static function getImageExtensionFromMimeType($mimeType)
	{
		$extensions = [
			'jpeg' => 'jpg',
			'png' => 'png',
			'gif' => 'gif',
		];

		return $extensions[$mimeType] ?? 'jpg'; // Default to 'jpg' if not recognized
	}
}