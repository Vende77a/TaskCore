<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const sidebarOpen = ref(false)

const user = computed(() => page.props.auth?.user ?? null)
</script>

<template>
    <div class="app-shell">
        <aside class="sidebar" :class="{ open: sidebarOpen }">
            <div class="sidebar-top">
                <Link :href="route('dashboard')" class="brand">
                    <div class="brand-logo-image-wrap">
                        <img
                            src="/images/taskcore-logo.png"
                            alt="TaskCore logo"
                            class="brand-logo-image"
                        />
                    </div>

                    <div class="brand-text">
                        <div class="brand-title">TaskCore</div>
                        <div class="brand-subtitle">Project Management</div>
                    </div>
                </Link>

                <button class="mobile-close" @click="sidebarOpen = false">
                    ✕
                </button>
            </div>

            <nav class="sidebar-nav">
                <Link
                    :href="route('dashboard')"
                    class="nav-link"
                    :class="{ active: route().current('dashboard') }"
                >
                    Главная страница
                </Link>

                <Link
                    :href="route('projects.index')"
                    class="nav-link"
                    :class="{ active: route().current('projects.*') }"
                >
                    Проекты
                </Link>

                <Link
                    :href="route('profile.edit')"
                    class="nav-link"
                    :class="{ active: route().current('profile.*') }"
                >
                    Профиль
                </Link>
            </nav>

            <div class="sidebar-bottom" v-if="user">
                <div class="user-card">
                    <div class="user-name">{{ user.name }}</div>
                    <div class="user-email">{{ user.email }}</div>
                </div>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="logout-btn"
                >
                    Выйти
                </Link>
            </div>
        </aside>

        <div
            v-if="sidebarOpen"
            class="sidebar-overlay"
            @click="sidebarOpen = false"
        ></div>

        <div class="main-area">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="burger" @click="sidebarOpen = true">☰</button>

                    <div class="page-heading">
                        <div class="page-title">
                            <slot name="header" />
                        </div>
                    </div>
                </div>
            </header>

            <main class="page-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.app-shell {
    min-height: 100vh;
    display: flex;
    background: #f3f4f6;
}

.sidebar {
    width: 260px;
    background: #111827;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px 16px;
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 50;
    transform: translateX(0);
    transition: transform 0.25s ease;
}

.sidebar-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    text-decoration: none;
}

.brand-title {
    font-weight: 700;
    font-size: 18px;
}

.brand-subtitle {
    font-size: 12px;
    color: #9ca3af;
}

.mobile-close {
    display: none;
    background: transparent;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
}

.nav-link {
    display: block;
    padding: 12px 14px;
    border-radius: 10px;
    color: #d1d5db;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
}

.nav-link:hover {
    background: #1f2937;
    color: white;
}

.nav-link.active {
    background: #2563eb;
    color: white;
}

.sidebar-bottom {
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.user-card {
    padding: 12px;
    border-radius: 12px;
    background: #1f2937;
}

.user-name {
    font-weight: 600;
}

.user-email {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
    word-break: break-word;
}

.logout-btn {
    width: 100%;
    border: none;
    border-radius: 10px;
    padding: 10px 12px;
    background: #ef4444;
    color: white;
    cursor: pointer;
    font-weight: 600;
}

.main-area {
    flex: 1;
    margin-left: 260px;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.topbar {
    height: 72px;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
}

.burger {
    display: none;
    background: transparent;
    border: none;
    font-size: 22px;
    cursor: pointer;
}

.page-content {
    padding: 24px;
}

.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.35);
    z-index: 40;
}

@media (max-width: 900px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.open {
        transform: translateX(0);
    }

    .main-area {
        margin-left: 0;
    }

    .burger {
        display: inline-block;
    }

    .mobile-close {
        display: inline-block;
    }

    .topbar {
        padding: 0 16px;
    }

    .page-content {
        padding: 16px;
    }

    .page-title {
        font-size: 20px;
    }
}

.brand-logo-image-wrap {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    padding: 6px;
}

.brand-logo-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
</style>
