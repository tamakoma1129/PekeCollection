import forms from '@tailwindcss/forms';
import colors from "tailwindcss/colors";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree','YakuHanJPs', 'Noto Sans JP', 'sans-serif'],
            },
            colors: {
                // miku-500の時#ffffffとコントラスト比3:1以上、miku-600の時は4.5:1以上
                "miku": {
                    '50': '#f1fcf9',
                    '100': '#d1f6ee',
                    '200': '#a3ecdf',
                    '300': '#6edaca',
                    '400': '#40c1b3',
                    '500': '#26a69a',
                    '600': '#1c847c',
                    '700': '#1b6a65',
                    '800': '#1a5552',
                    '900': '#1a4745',
                    '950': '#092a29',
                },
                // teto-500の時#ffffffとコントラスト比3:1以上、teto-600の時は4.5:1以上
                "teto": colors.red,
                // sumi-500で#ffffffと4.5:1、3:1以上
                "sumi": colors.neutral
            },
            animation: {
                "scale-up-bottom": "scale-up-bottom 0.2s cubic-bezier(0.390, 0.575, 0.565, 1.000)   both"
            },
            keyframes: {
                "scale-up-bottom": {
                    "0%": {
                        "transform": "scaleY(0)",
                        "transform-origin": "bottom"
                    },
                    to: {
                        "transform": "scaleY(1)",
                        "transform-origin": "bottom"
                    }
                }
            }
        },
        // z-indexは「-inf,-1,0,1,inf」の5段階のみで扱う。
        zIndex: {
            0: 0,
            1: 1,
            inf: "calc(infinity)",
        },
        // 基本768pxのブレイクポイント1つで扱っていく。厳しいときだけ480pxや1024pxも扱う。
        screens: {
            "sm": "480px",
            // => @media (min-width: 480px) { ... }

            "md": "768px",
            // => @media (min-width: 768px) { ... }

            'lg': '1024px',
            // => @media (min-width: 1024px) { ... }
        },
        //　一般化されたフィボナッチ数列を採用(詳しくはTailwind CSS 実践入門を参照)
        spacing: {
            "0": '0px',
            "4": '4px',
            "8": '8px',
            "16": '16px',
            "24": '24px',
            "40": '40px',
            "64": '64px',
            "104": '104px',
            "168": '168px',
            "272": '272px',
            "full": '100%',
        },
        fontWeight: {
            "normal": "400",
            "semibold": "600",
            "bold": "700",
        }
    },

    plugins: [forms],
};
