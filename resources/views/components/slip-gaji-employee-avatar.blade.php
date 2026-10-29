@props(['user' => null, 'size' => 44])

@php
    $photo = null;
    $disk = \Illuminate\Support\Facades\Storage::disk(config('jetstream.profile_photo_disk', 'public'));

    if ($user?->profile_photo_path && $disk->exists($user->profile_photo_path)) {
        $mime = $disk->mimeType($user->profile_photo_path) ?: 'image/jpeg';
        $photo = 'data:' . $mime . ';base64,' . base64_encode($disk->get($user->profile_photo_path));
    } elseif ($user?->foto_url) {
        $path = ltrim((string) $user->foto_url, '/');
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:image/')) {
            $photo = $path;
        } else {
            $relativePath = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;
            $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($publicDisk->exists($relativePath)) {
                $mime = $publicDisk->mimeType($relativePath) ?: 'image/jpeg';
                $photo = 'data:' . $mime . ';base64,' . base64_encode($publicDisk->get($relativePath));
            } elseif (file_exists(public_path($path))) {
                $mime = mime_content_type(public_path($path)) ?: 'image/jpeg';
                $photo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents(public_path($path)));
            }
        }
    }

    $initials = collect(preg_split('/\s+/', trim($user?->name ?? 'Karyawan')))
        ->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
@endphp

<span style="display:inline-flex; width:{{ $size }}px; height:{{ $size }}px; flex:0 0 {{ $size }}px; overflow:hidden; align-items:center; justify-content:center; border:1px solid #DDD6FE; border-radius:50%; background:#F3E8FF; color:#5E17EB; font:800 {{ max(12, (int) ($size * .34)) }}px Arial,sans-serif;">
    @if($photo)
        <img src="{{ $photo }}" alt="Foto {{ $user?->name ?? 'karyawan' }}" style="display:block; width:100%; height:100%; object-fit:cover;">
    @else
        {{ $initials ?: 'K' }}
    @endif
</span>
