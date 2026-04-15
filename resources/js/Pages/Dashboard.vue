<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    stats: Object,
    recentProjects: Array,
    recentTasks: Array,
})

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleDateString('ru-RU')
}

const statusLabel = (status) => {
    switch (status) {
        case 'backlog':
            return 'Backlog'
        case 'in_progress':
            return 'In Progress'
        case 'review':
            return 'Review'
        case 'done':
            return 'Done'
        default:
            return status
    }
}

const priorityLabel = (priority) => {
    if (priority === 3) return 'Высокий'
    if (priority === 2) return 'Средний'
    return 'Низкий'
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            Dashboard
        </template>

        <div class="dashboard-page">
            <section class="hero-card">
                <div>
                    <h1>Добро пожаловать в TaskCore</h1>
                    <p>
                        Управляй проектами, задачами и прогрессом команды из одного места.
                    </p>
                </div>

                <div class="hero-actions">
                    <Link :href="route('projects.index')" class="primary-btn">
                        Открыть проекты
                    </Link>
                </div>
            </section>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Проекты</div>
                    <div class="stat-value">{{ stats.projects }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Все задачи</div>
                    <div class="stat-value">{{ stats.tasks }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">В работе</div>
                    <div class="stat-value">{{ stats.in_progress }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">На проверке</div>
                    <div class="stat-value">{{ stats.review }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Готово</div>
                    <div class="stat-value">{{ stats.done }}</div>
                </div>
            </section>

            <section class="dashboard-grid">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Последние проекты</h2>
                        <Link :href="route('projects.index')" class="panel-link">
                            Все проекты
                        </Link>
                    </div>

                    <div v-if="recentProjects.length" class="project-list">
                        <Link
                            v-for="project in recentProjects"
                            :key="project.id"
                            :href="route('projects.show', project.id)"
                            class="project-item"
                        >
                            <div class="project-title">{{ project.title }}</div>
                            <div class="project-description">
                                {{ project.description || 'Без описания' }}
                            </div>
                            <div class="project-date">
                                Создан: {{ formatDate(project.created_at) }}
                            </div>
                        </Link>
                    </div>

                    <div v-else class="empty-state">
                        У тебя пока нет проектов.
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2>Последние задачи</h2>
                    </div>

                    <div v-if="recentTasks.length" class="task-list">
                        <div
                            v-for="task in recentTasks"
                            :key="task.id"
                            class="task-item"
                        >
                            <div class="task-top">
                                <div class="task-title">{{ task.title }}</div>
                                <span class="task-status" :class="task.status">
                                    {{ statusLabel(task.status) }}
                                </span>
                            </div>

                            <div class="task-meta">
                                <span>Проект: {{ task.project?.title || '—' }}</span>
                                <span>Приоритет: {{ priorityLabel(task.priority) }}</span>
                                <span>Дедлайн: {{ formatDate(task.due_date) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        У тебя пока нет задач.
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.hero-card {
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    color: white;
    border-radius: 20px;
    padding: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.hero-card h1 {
    margin: 0 0 8px 0;
    font-size: 32px;
    font-weight: 700;
}

.hero-card p {
    margin: 0;
    max-width: 640px;
    color: rgba(255, 255, 255, 0.9);
}

.hero-actions {
    display: flex;
    align-items: center;
}

.primary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background: white;
    color: #1d4ed8;
    padding: 12px 18px;
    border-radius: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
}

.stat-card {
    background: white;
    border-radius: 18px;
    padding: 20px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.stat-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #111827;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 24px;
}

.panel {
    background: white;
    border-radius: 20px;
    padding: 22px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}

.panel-header h2 {
    margin: 0;
    font-size: 20px;
    color: #111827;
}

.panel-link {
    text-decoration: none;
    color: #2563eb;
    font-weight: 600;
}

.project-list,
.task-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.project-item {
    text-decoration: none;
    color: inherit;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    background: #fff;
}

.project-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

.project-title,
.task-title {
    font-weight: 700;
    color: #111827;
}

.project-description {
    margin-top: 6px;
    color: #6b7280;
    font-size: 14px;
}

.project-date {
    margin-top: 10px;
    font-size: 13px;
    color: #9ca3af;
}

.task-item {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px;
    background: #fff;
}

.task-top {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
}

.task-status {
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 600;
    white-space: nowrap;
}

.task-status.backlog {
    background: #f3f4f6;
    color: #374151;
}

.task-status.in_progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.task-status.review {
    background: #fef3c7;
    color: #b45309;
}

.task-status.done {
    background: #dcfce7;
    color: #15803d;
}

.task-meta {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 14px;
    color: #6b7280;
}

.empty-state {
    border: 1px dashed #d1d5db;
    border-radius: 14px;
    padding: 20px;
    text-align: center;
    color: #6b7280;
    background: #f9fafb;
}

@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .hero-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .hero-card h1 {
        font-size: 26px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 520px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
