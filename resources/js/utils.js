export function getPrivateStoragePath(path) {
    return `private/${path}`;
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
