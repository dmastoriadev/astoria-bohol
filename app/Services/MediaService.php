<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class MediaService
{
    public function __construct(
        protected string $disk = 'public',
        protected int $maxWidth = 2560,
        protected int $quality = 82
    ) {}

    /**
     * From an UploadedFile -> stores as WEBP and returns info.
     */
    public function fromUpload(UploadedFile $file, string $dir = 'uploads/images')
    : array {
        $bin = file_get_contents($file->getRealPath());
        $extless = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'image';
        return $this->storeAsWebp($bin, $extless, $dir);
    }

    /**
     * From a remote URL (gallery or absolute image URL).
     */
    public function fromUrl(string $url, string $dir = 'uploads/images')
    : array {
        $url = trim($url);
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid image URL');
        }
        $bin = @file_get_contents($url);
        if ($bin === false) {
            throw new \RuntimeException('Failed to download image');
        }
        // best-effort filename
        $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'image';
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        return $this->storeAsWebp($bin, $filename, $dir);
    }

    /**
     * Core: decode -> resize (if needed) -> WEBP -> store.
     */
    protected function storeAsWebp(string $binary, string $filename, string $dir)
    : array {
        // decode
        $img = Image::make($binary);

        // guard against non-image
        if (! $img->width() || ! $img->height()) {
            throw new \RuntimeException('Not a valid image');
        }

        // scale down large images (keep aspect)
        if ($img->width() > $this->maxWidth) {
            $img->resize($this->maxWidth, null, function ($c) { $c->aspectRatio(); $c->upsize(); });
        }

        // ensure directory like /uploads/images/2025/03
        $dated = trim($dir, '/').'/'.date('Y').'/'.date('m');
        $slug = Str::slug(Str::limit($filename, 80, ''));
        if ($slug === '') $slug = 'image';
        $name = $slug.'-'.Str::random(6).'.webp';

        $path = $dated.'/'.$name;

        // encode webp & store
        $webp = (string) $img->encode('webp', $this->quality);
        Storage::disk($this->disk)->put($path, $webp, ['visibility' => 'public']);

        return [
            'disk'   => $this->disk,
            'path'   => $path,
            'url'    => Storage::disk($this->disk)->url($path),
            'width'  => $img->width(),
            'height' => $img->height(),
            'mime'   => 'image/webp',
        ];
    }
}
