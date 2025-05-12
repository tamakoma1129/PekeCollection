<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import { Link } from "@inertiajs/vue3";
import MediaToolbar from "@/Layouts/AuthenticatedLayout/Partials/MediaToolbar.vue";
import FileUploader from "@/Layouts/AuthenticatedLayout/Partials/FileUploader.vue";
import UploadQueue from "@/Layouts/AuthenticatedLayout/Partials/UploadQueue.vue";
</script>

<template>
    <div class="flex h-screen">
        <!-- サイドバー -->
        <nav class="bg-white min-w-168 fixed">
            <div class="flex flex-col px-16 items-end pt-8">
                <!-- ロゴ -->
                <div class="flex shrink-0 items-center mr-auto mb-40">
                    <Link
                        :href="route('media.index', { mediaType: 'all' })"
                        class="flex items-center"
                    >
                        <ApplicationLogo
                            class="block h-40 w-auto fill-current text-gray-800"
                        />
                        <p class="text-xl font-semibold ml-8">ペケコレ</p>
                    </Link>
                </div>

                <!-- Navigation Links -->
                <NavLink
                    :href="route('dashboard')"
                    :active="route().current('dashboard')"
                >
                    ホーム
                </NavLink>
                <NavLink
                    :href="route('media.index', { mediaType: 'all' })"
                    :active="
                        route().current('media.index', { mediaType: 'all' })
                    "
                >
                    <i-token-branded-media class="w-24 h-24 mb-4 mr-16" />
                    全部
                </NavLink>
                <NavLink
                    :href="route('media.index', { mediaType: 'image' })"
                    :active="
                        route().current('media.index', { mediaType: 'image' })
                    "
                >
                    <i-pepicons-pop-photo
                        class="w-24 h-24 mb-4 mr-16 bg-green-500 p-4 text-white"
                    />
                    画像
                </NavLink>
                <NavLink
                    :href="route('media.index', { mediaType: 'video' })"
                    :active="
                        route().current('media.index', { mediaType: 'video' })
                    "
                >
                    <i-pepicons-pop-clapperboard
                        class="w-24 h-24 mb-4 mr-16 bg-blue-500 p-4 text-white"
                    />
                    動画
                </NavLink>
                <NavLink
                    :href="route('media.index', { mediaType: 'audio' })"
                    :active="
                        route().current('media.index', { mediaType: 'audio' })
                    "
                    class="flex items-center"
                >
                    <i-fa6-solid-headphones
                        class="w-24 h-24 mb-4 mr-16 bg-teto-500 p-4 text-white"
                    />
                    音源
                </NavLink>
                <NavLink
                    :href="route('media.index', { mediaType: 'manga' })"
                    :active="
                        route().current('media.index', { mediaType: 'manga' })
                    "
                    class="flex items-center"
                >
                    <i-bi-book
                        class="w-24 h-24 mb-4 mr-16 bg-violet-500 p-4 text-white"
                    />
                    漫画
                </NavLink>
                <NavLink
                    :href="route('manga.create')"
                    :active="route().current('manga.create')"
                    class="flex items-center mt-40"
                >
                    <i-fluent-book-add-24-filled
                        class="w-24 h-24 mb-4 mr-16 bg-violet-500 p-4 text-white"
                    />
                    漫画追加
                </NavLink>
            </div>
        </nav>
        <!-- 右要素 -->
        <div class="ml-168 w-full flex flex-col overflow-x-hidden">
            <!-- 上部ナビバー -->
            <nav
                class="flex items-center justify-end bg-white pr-24 h-40 sticky top-0 z-1 gap-x-16"
            >
                <a href="/telescope" target="_blank" class="hover:text-miku-500"
                    >Telescope</a
                >
                <a href="/pulse" target="_blank" class="hover:text-miku-500"
                    >Pulse</a
                >
                <!-- ユーザー名ドロップダウン -->
                <div class="relative ms-16">
                    <Dropdown align="right" width="168">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-white p-8 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                >
                                    ユーザー

                                    <svg
                                        class="-me-4 ms-8 h-16 w-16"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </span>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('password.edit')">
                                パスワードの変更
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                ログアウト
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <!-- メイン -->
            <div
                class="flex-1 overflow-y-auto rounded-tl-3xl bg-sumi-200 p-16 pb-168"
            >
                <!-- メインコンテンツ -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
        <FileUploader v-if="route().current() !== 'manga.create'" />
        <UploadQueue />
        <MediaToolbar />
    </div>
</template>
