<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'

defineProps({
    projects: Array,
})

const form = useForm({
    title: '',
    description: '',
})

const submit = () => {
    form.post(route('projects.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}

const deleteProject = (projectId) => {
    if (confirm('Удалить проект?')) {
        router.delete(route('projects.destroy', projectId), {
            preserveScroll: true,
        })
    }
}

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleDateString('ru-RU')
}
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <template #header>
            Проекты
        </template>

        <div class="projects-page">
            <section class="projects-hero">
                <div>
                    <h1>Проекты</h1>
                    <p>
                        Создавай проекты, управляй задачами и открывай канбан-доску
                        для каждого рабочего пространства.
                    </p>
                </div>

            </section>

            <section class="projects-grid">
                <div class="create-panel">
                    <div class="panel-header">
                        <h2>Создать проект</h2>
                        <p>Добавь новый проект в систему TaskCore.</p>
                    </div>

                    <form class="project-form" @submit.prevent="submit">
                        <div class="form-group">
                            <label>Название проекта</label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Например: CRM System"
                            />
                            <small v-if="form.errors.title" class="field-error">
                                {{ form.errors.title }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Описание</label>
                            <textarea
                                v-model="form.description"
                                rows="5"
                                placeholder="Кратко опиши проект"
                            ></textarea>
                            <small v-if="form.errors.description" class="field-error">
                                {{ form.errors.description }}
                            </small>
                        </div>

                        <button
                            type="submit"
                            class="primary-btn"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Создание...' : 'Создать проект' }}
                        </button>
                    </form>
                </div>

                <div class="projects-panel">
                    <div class="panel-header">
                        <h2>Список проектов</h2>
                        <p>Выбери проект, чтобы открыть его доску.</p>
                    </div>

                    <div v-if="projects.length" class="projects-list">
                        <div
                            v-for="project in projects"
                            :key="project.id"
                            class="project-card"
                        >
                            <div class="project-card-top">
                                <div>
                                    <div class="project-title">
                                        {{ project.title }}
                                    </div>
                                    <div class="project-description">
                                        {{ project.description || 'Без описания' }}
                                    </div>
                                </div>

                                <div class="project-badge">
                                    {{ project.tasks_count ?? 0 }} задач
                                </div>
                            </div>

                            <div class="project-meta">
                                Создан: {{ formatDate(project.created_at) }}
                            </div>

                            <div class="project-actions">
                                <Link
                                    :href="route('projects.show', project.id)"
                                    class="open-btn"
                                >
                                    Открыть
                                </Link>

                                <button
                                    class="delete-btn"
                                    @click="deleteProject(project.id)"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <div class="empty-title">Проектов пока нет</div>
                        <div class="empty-text">
                            Создай первый проект слева, и он появится в этом списке.
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.projects-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.projects-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    color: white;
    border-radius: 20px;
    padding: 28px;
}

.projects-hero h1 {
    margin: 0 0 8px 0;
    font-size: 32px;
}

.projects-hero p {
    margin: 0;
    max-width: 720px;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
}

.projects-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 24px;
    align-items: start;
}

.create-panel,
.projects-panel {
    background: white;
    border-radius: 20px;
    padding: 22px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.panel-header {
    margin-bottom: 18px;
}

.panel-header h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    color: #111827;
}

.panel-header p {
    margin: 0;
    color: #6b7280;
    line-height: 1.6;
}

.project-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 12px 14px;
    font: inherit;
    background: white;
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.primary-btn {
    border: none;
    border-radius: 12px;
    padding: 12px 16px;
    background: #2563eb;
    color: white;
    font-weight: 700;
    cursor: pointer;
}

.primary-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.field-error {
    color: #dc2626;
    font-size: 12px;
}

.projects-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.project-card {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 18px;
    background: #fff;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

.project-card-top {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: flex-start;
}

.project-title {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.project-description {
    margin-top: 8px;
    color: #6b7280;
    line-height: 1.6;
    font-size: 14px;
}

.project-badge {
    white-space: nowrap;
    font-size: 12px;
    font-weight: 700;
    color: #1d4ed8;
    background: #dbeafe;
    padding: 6px 10px;
    border-radius: 999px;
}

.project-meta {
    margin-top: 14px;
    font-size: 13px;
    color: #9ca3af;
}

.project-actions {
    margin-top: 16px;
    display: flex;
    gap: 10px;
}

.open-btn,
.delete-btn {
    border: none;
    border-radius: 10px;
    padding: 10px 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

.open-btn {
    background: #2563eb;
    color: white;
}

.delete-btn {
    background: #fee2e2;
    color: #991b1b;
}

.empty-state {
    border: 1px dashed #d1d5db;
    border-radius: 16px;
    padding: 28px;
    text-align: center;
    background: #f9fafb;
}

.empty-title {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.empty-text {
    margin-top: 8px;
    color: #6b7280;
    line-height: 1.6;
}

@media (max-width: 1100px) {
    .projects-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 700px) {
    .projects-hero {
        flex-direction: column;
    }

    .projects-hero h1 {
        font-size: 26px;
    }

    .project-card-top {
        flex-direction: column;
    }

    .project-actions {
        flex-direction: column;
    }
}
</style>
