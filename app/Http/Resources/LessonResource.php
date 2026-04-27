<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'level_id' => $this->level_id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'pdf_url' => $this->generatePdfUrl($this->pdf_url),
            'completed' => (bool) ($this->completed ?? false),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Generate URL untuk akses PDF via route /pdf/{file}
     */
    private function generatePdfUrl(?string $pdfUrl): ?string
    {
        if (empty($pdfUrl)) {
            return null;
        }

        // ✅ Jika sudah full URL (misalnya dari CDN / external)
        if (filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
            return $pdfUrl;
        }

        // Ambil nama file saja (hindari path traversal)
        $filename = basename($pdfUrl);

        // 🔥 Gunakan route Laravel untuk serve PDF
        return url('/pdf/' . $filename);
    }
}
