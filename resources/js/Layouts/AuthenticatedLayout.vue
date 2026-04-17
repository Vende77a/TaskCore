<script setup>
import { computed, ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const sidebarOpen = ref(false)

const user = computed(() => page.props.auth?.user ?? null)

const notificationsOpen = ref(false)
const notificationsLoading = ref(false)
const notifications = ref([])
const unreadCount = computed(() => page.props.auth?.unreadNotificationsCount ?? 0)

const formatDateTime = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleString('ru-RU')
}

const toggleNotifications = async () => {
    notificationsOpen.value = !notificationsOpen.value

    if (notificationsOpen.value && notifications.value.length === 0) {
        notificationsLoading.value = true

        try {
            const response = await fetch(route('notifications.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })

            const data = await response.json()
            notifications.value = data.notifications ?? []
        } finally {
            notificationsLoading.value = false
        }
    }
}

const markAllNotificationsRead = () => {
    router.post(route('notifications.readAll'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            notifications.value = notifications.value.map(item => ({
                ...item,
                read_at: item.read_at ?? new Date().toISOString(),
            }))
        }
    })
}
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

                <div class="notifications-wrapper">
                    <button
                        type="button"
                        class="notifications-button"
                        @click="toggleNotifications"
                    >
                        🔔
                        <span v-if="unreadCount > 0" class="notifications-badge">
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
                    </button>

                    <div v-if="notificationsOpen" class="notifications-dropdown">
                        <div class="notifications-dropdown-header">
                            <div>
                                <strong>Уведомления</strong>
                            </div>

                            <button
                                v-if="unreadCount > 0"
                                type="button"
                                class="mark-read-btn"
                                @click="markAllNotificationsRead"
                            >
                                Прочитать все
                            </button>
                        </div>

                        <div v-if="notificationsLoading" class="notifications-state">
                            Загрузка...
                        </div>

                        <div
                            v-else-if="notifications.length"
                            class="notifications-list"
                        >
                            <div
                                v-for="notification in notifications"
                                :key="notification.id"
                                :class="['notification-item', { unread: !notification.read_at }]"
                            >
                                <div class="notification-item-top">
                    <span :class="['notification-type', `type-${notification.type}`]">
                        {{ notification.type || 'notification' }}
                    </span>
                                    <span class="notification-date">
                        {{ formatDateTime(notification.created_at) }}
                    </span>
                                </div>

                                <div class="notification-message">
                                    {{ notification.message }}
                                </div>
                            </div>
                        </div>

                        <div v-else class="notifications-state">
                            Пока уведомлений нет.
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

.notifications-wrapper {
    position: relative;
}

.notifications-button {
    position: relative;
    width: 40px;
    height: 40px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    font-size: 18px;
}

.notifications-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    background: #dc2626;
    color: white;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.notifications-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 360px;
    max-width: calc(100vw - 32px);
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
    padding: 14px;
    z-index: 100;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notifications-dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.mark-read-btn {
    background: #eef2ff;
    color: #3730a3;
    border: none;
    border-radius: 10px;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 420px;
    overflow-y: auto;
}

.notification-item {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    background: #f9fafb;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.notification-item.unread {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.notification-item-top {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.notification-type {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

.type-added_to_project {
    background: #dbeafe;
    color: #1d4ed8;
}

.type-task_assigned {
    background: #dcfce7;
    color: #15803d;
}

.type-task_commented {
    background: #fef3c7;
    color: #b45309;
}

.notification-date {
    font-size: 12px;
    color: #6b7280;
}

.notification-message {
    font-size: 14px;
    color: #111827;
    line-height: 1.5;
}

.notifications-state {
    padding: 14px;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    background: #f9fafb;
    color: #6b7280;
    font-size: 14px;
    text-align: center;
}
</style>
