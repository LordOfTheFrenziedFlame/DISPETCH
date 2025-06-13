<?php

namespace App\Components;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SaveMedia
{
    public array $file = [];

    public string $comment = '';

    public string $attachable_type;
    public int $attachable_id;

    // Максимальный размер файла в байтах (10MB)
    private const MAX_FILE_SIZE = 10240 * 1024;
    
    // Разрешенные MIME типы
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];

    // Разрешенные расширения
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'
    ];

    // Запрещенные расширения (исполняемые файлы)
    private const FORBIDDEN_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'php', 'asp', 'jsp'
    ];

    // Максимальная длина имени файла
    private const MAX_FILENAME_LENGTH = 255;

    public function save(bool $withFlash = true): array|false
    {
        if (empty($this->file)) {
            if ($withFlash) session()->flash('error', 'Файлы не выбраны');
            return false;
        }

        $savedFiles = [];
        $errors = [];

        foreach ($this->file as $file) {
            if (!$file->isValid()) {
                $errors[] = "Файл {$file->getClientOriginalName()} поврежден";
                continue;
            }

            // Проверка длины имени файла
            if (strlen($file->getClientOriginalName()) > self::MAX_FILENAME_LENGTH) {
                $errors[] = "Имя файла {$file->getClientOriginalName()} слишком длинное";
                continue;
            }

            // Проверка на запрещенные символы в имени файла
            if (preg_match('/[<>:"|?*\x00-\x1f]/', $file->getClientOriginalName())) {
                $errors[] = "Имя файла {$file->getClientOriginalName()} содержит недопустимые символы";
                continue;
            }

            // Проверка размера файла
            if ($file->getSize() > self::MAX_FILE_SIZE) {
                $errors[] = "Файл {$file->getClientOriginalName()} превышает максимальный размер 10MB";
                continue;
            }

            // Проверка расширения файла на запрещенные
            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, self::FORBIDDEN_EXTENSIONS)) {
                $errors[] = "Файл {$file->getClientOriginalName()} имеет запрещенное расширение";
                Log::warning('Попытка загрузки файла с запрещенным расширением', [
                    'filename' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'attachable_type' => $this->attachable_type,
                    'attachable_id' => $this->attachable_id,
                ]);
                continue;
            }

            // Проверка MIME типа
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                $errors[] = "Файл {$file->getClientOriginalName()} имеет неразрешенный тип";
                Log::warning('Попытка загрузки файла неразрешенного типа', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'attachable_type' => $this->attachable_type,
                    'attachable_id' => $this->attachable_id,
                ]);
                continue;
            }

            // Проверка расширения файла
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                $errors[] = "Файл {$file->getClientOriginalName()} имеет неразрешенное расширение";
                continue;
            }

            // Дополнительная проверка заголовков файла
            if (!$this->validateFileSignature($file, $extension)) {
                $errors[] = "Файл {$file->getClientOriginalName()} не соответствует заявленному типу";
                Log::warning('Файл не прошел проверку сигнатуры', [
                    'filename' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'attachable_type' => $this->attachable_type,
                    'attachable_id' => $this->attachable_id,
                ]);
                continue;
            }

            // Генерируем безопасное имя файла
            $safeFilename = $this->generateSafeFilename($file);

            try {
                $path = $file->storeAs('attachments', $safeFilename, 'public');

                Attachment::create([
                    'path'            => $path,
                    'filename'        => $file->getClientOriginalName(),
                    'mime_type'       => $mimeType,
                    'attachable_type' => $this->attachable_type,
                    'attachable_id'   => $this->attachable_id,
                    'comment'         => $this->comment,
                ]);

                $savedFiles[] = $path;

                Log::info('Файл успешно загружен', [
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_path' => $path,
                    'attachable_type' => $this->attachable_type,
                    'attachable_id' => $this->attachable_id,
                ]);

            } catch (\Exception $e) {
                $errors[] = "Ошибка при сохранении файла {$file->getClientOriginalName()}";
                Log::error('Ошибка при загрузке файла', [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'attachable_type' => $this->attachable_type,
                    'attachable_id' => $this->attachable_id,
                ]);
            }
        }

        if ($withFlash) {
            if (!empty($errors)) {
                session()->flash('error', implode('; ', $errors));
            }
            if (!empty($savedFiles)) {
                session()->flash('success', 'Файлы успешно сохранены: ' . count($savedFiles));
            }
        }

        return !empty($savedFiles) ? $savedFiles : false;
    }

    /**
     * Генерирует безопасное имя файла
     */
    private function generateSafeFilename($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $randomString = bin2hex(random_bytes(8));
        
        return "{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Проверяет сигнатуру файла по его заголовкам
     */
    private function validateFileSignature($file, string $extension): bool
    {
        $handle = fopen($file->getPathname(), 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 16);
        fclose($handle);

        $signatures = [
            'jpg' => [
                "\xFF\xD8\xFF\xE0",
                "\xFF\xD8\xFF\xE1",
                "\xFF\xD8\xFF\xE8",
                "\xFF\xD8\xFF\xDB"
            ],
            'jpeg' => [
                "\xFF\xD8\xFF\xE0",
                "\xFF\xD8\xFF\xE1",
                "\xFF\xD8\xFF\xE8",
                "\xFF\xD8\xFF\xDB"
            ],
            'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'pdf' => ["\x25\x50\x44\x46"],
            'doc' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
            'docx' => ["\x50\x4B\x03\x04"],
            'xls' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
            'xlsx' => ["\x50\x4B\x03\x04"],
            'ppt' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
            'pptx' => ["\x50\x4B\x03\x04"]
        ];

        if (!isset($signatures[$extension])) {
            return true; // Если нет известной сигнатуры, пропускаем проверку
        }

        foreach ($signatures[$extension] as $signature) {
            if (substr($header, 0, strlen($signature)) === $signature) {
                return true;
            }
        }

        return false;
    }

    public function getMedia()
    {
        return Attachment::where('attachable_type', $this->attachable_type)
            ->where('attachable_id', $this->attachable_id)
            ->get();
    }

    public function deleteMedia(): bool
    {
        $files = $this->getMedia();

        if ($files->isEmpty()) {
            return false;
        }

        Storage::disk('public')->delete($files->pluck('path')->toArray());

        foreach ($files as $file) {
            $file->delete();
        }

        return true;
    }

    public function updateMedia(): bool
    {
        if (empty($this->file)) {
            return false;
        }

        $this->deleteMedia();

        return !empty($this->save(false));
    }
}
