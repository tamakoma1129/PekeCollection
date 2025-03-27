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
