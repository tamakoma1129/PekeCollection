export function getPrivateStoragePath(path) {
    return path ? `private/${path}` : null;
}

export function convertToValidWindowsFileName(fileName) {
    const replaceMap = {
        "\\": "￥",
        "/": "／",
        ":": "：",
        "*": "＊",
        "?": "？",
        '"': "”",
        "<": "＜",
        ">": "＞",
        "|": "｜"
    };

    return fileName.replace(/[\\/:*?"<>|]/g, (match) => replaceMap[match]);
}

export const formatSecondsToTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = Math.floor(seconds % 60);

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, "0")}:${remainingSeconds.toString().padStart(2, "0")}`;
    }
    return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
}
