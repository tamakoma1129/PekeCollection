<?php

namespace App\Enums;

/**
 * このEnumはメディアでフォルダを分けるときに使う値を定義している。
 * uploads/{MediaFolderTypes} や extras/{MediaFolderTypes} など。
 *
 */
enum MediaFolderTypes: string
{
    case IMAGES = 'images';
    case VIDEOS = 'videos';
    case AUDIOS = 'audios';
    case MANGAS = 'mangas';
}
