<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

final class QrCodeDataUri
{
    /**
     * SVG QR code as a data: URI (for <img src="...">).
     *
     * @param  positive-int  $imageSizePixels  Total width/height of the image (BaconQrCode RendererStyle size).
     */
    public static function svgDataUri(string $value, int $imageSizePixels = 220): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($imageSizePixels),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($value));
    }
}
