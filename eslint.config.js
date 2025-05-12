import js from "@eslint/js";
import globals from "globals";
import tseslint from "typescript-eslint";
import pluginVue from "eslint-plugin-vue";
import eslintConfigPrettier from "eslint-config-prettier/flat";
import eslintPluginPrettierRecommended from "eslint-plugin-prettier/recommended";
import { defineConfig } from "eslint/config";

export default defineConfig([
    {
        ignores: ["public/build/", "storage/", "vendor/"], // デフォで["**/node_modules/", ".git/"]が付いている
    },

    {
        files: ["**/*.{js,mjs,cjs,ts,vue}"],
        plugins: { js },
        extends: ["js/recommended"],
    },
    {
        files: ["**/*.{js,mjs,cjs,ts,vue}"],
        languageOptions: {
            globals: { ...globals.browser, ...globals.node },
        },
    },

    tseslint.configs.recommended,

    pluginVue.configs["flat/essential"],
    {
        files: ["**/*.vue"],
        languageOptions: {
            parserOptions: { parser: tseslint.parser },
        },
    },
    eslintPluginPrettierRecommended,
    eslintConfigPrettier,
]);
