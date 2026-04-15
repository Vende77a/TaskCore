<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import draggable from 'vuedraggable'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    project: Object,
    users: Array,
})

const columns = ref({
    backlog: [],
    in_progress: [],
    review: [],
    done: []
})

const syncColumns = () => {
    const tasks = props.project.tasks

    columns.value.backlog = tasks
        .filter(t => t.status === 'backlog')
        .sort((a, b) => a.order - b.order)

    columns.value.in_progress = tasks
        .filter(t => t.status === 'in_progress')
        .sort((a, b) => a.order - b.order)

    columns.value.review = tasks
        .filter(t => t.status === 'review')
        .sort((a, b) => a.order - b.order)

    columns.value.done = tasks
        .filter(t => t.status === 'done')
        .sort((a, b) => a.order - b.order)
}

syncColumns()
watch(() => props.project.tasks, syncColumns, { deep: true })

const form = useForm({
    title: '',
    project_id: props.project.id,
    priority: 1,
})

const submit = () => {
    form.post(route('tasks.store'), {
        onSuccess: () => form.reset('title')
    })
}

const updateStatus = (taskId, newStatus) => {
    router.patch(route('tasks.update', taskId), {
        status: newStatus
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const deleteTask = (taskId) => {
    if (confirm('Удалить задачу?')) {
        router.delete(route('tasks.destroy', taskId), {
            preserveScroll: true,
        })
    }
}

const onEnd = () => {
    const payload = []

    Object.entries(columns.value).forEach(([status, tasks]) => {
        tasks.forEach((task, index) => {
            payload.push({
                id: task.id,
                order: index,
                status: status
            })
        })
    })

    router.post(route('tasks.reorder'), {
        tasks: payload
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

/* drawer */
const isDrawerOpen = ref(false)
const selectedTaskId = ref(null)

const drawerForm = useForm({
    title: '',
    description: '',
    status: 'backlog',
    priority: 1,
    due_date: '',
    user_id: '',
})

const selectedTask = computed(() => {
    const allTasks = Object.values(columns.value).flat()
    return allTasks.find(task => task.id === selectedTaskId.value) ?? null
})

const formatForDateTimeInput = (value) => {
    if (!value) return ''
    return value.slice(0, 16)
}

const formatDateTime = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleString('ru-RU')
}

const formatShortDate = (value) => {
    if (!value) return ''
    return new Date(value).toLocaleDateString('ru-RU')
}

const priorityLabel = (priority) => {
    if (priority === 3) return 'Высокий'
    if (priority === 2) return 'Средний'
    return 'Низкий'
}

const fillDrawerForm = (task) => {
    drawerForm.title = task.title ?? ''
    drawerForm.description = task.description ?? ''
    drawerForm.status = task.status ?? 'backlog'
    drawerForm.priority = task.priority ?? 1
    drawerForm.due_date = formatForDateTimeInput(task.due_date)
    drawerForm.user_id = task.user_id ?? ''
}

const openTaskDrawer = (task) => {
    selectedTaskId.value = task.id
    fillDrawerForm(task)
    drawerForm.clearErrors()
    isDrawerOpen.value = true
}

const closeTaskDrawer = () => {
    isDrawerOpen.value = false
    selectedTaskId.value = null
    drawerForm.clearErrors()
}

const saveTaskDetails = () => {
    if (!selectedTaskId.value) return

    drawerForm.patch(route('tasks.update', selectedTaskId.value), {
        preserveState: true,
        preserveScroll: true,
    })
}

const commentForm = useForm({
    body: '',
})

const attachmentForm = useForm({
    file: null,
})

const submitComment = () => {
    if (!selectedTask.value) return

    commentForm.post(route('tasks.comments.store', selectedTask.value.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => commentForm.reset(),
    })
}

const onFileChange = (event) => {
    attachmentForm.file = event.target.files[0]
}

const submitAttachment = () => {
    if (!selectedTask.value || !attachmentForm.file) return

    attachmentForm.post(route('tasks.attachments.store', selectedTask.value.id), {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        onSuccess: () => {
            attachmentForm.reset()
        },
    })
}

const deleteComment = (commentId) => {
    if (confirm('Удалить комментарий?')) {
        router.delete(route('comments.destroy', commentId), {
            preserveScroll: true,
            preserveState: true,
        })
    }
}

const deleteAttachment = (attachmentId) => {
    if (confirm('Удалить файл?')) {
        router.delete(route('attachments.destroy', attachmentId), {
            preserveScroll: true,
            preserveState: true,
        })
    }
}

const formatFileSize = (bytes) => {
    if (!bytes) return '0 Б'
    if (bytes < 1024) return `${bytes} Б`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} КБ`
    return `${(bytes / (1024 * 1024)).toFixed(1)} МБ`
}
</script>

<template>
    <Head :title="project.title" />

    <AuthenticatedLayout>
        <template #header>
            {{ project.title }}
        </template>

        <div class="project-show-page">
            <section class="project-hero">
                <div class="project-hero-left">
                    <div class="breadcrumbs">
                        <Link :href="route('dashboard')">Dashboard</Link>
                        <span>/</span>
                        <Link :href="route('projects.index')">Projects</Link>
                        <span>/</span>
                        <span class="current">{{ project.title }}</span>
                    </div>

                    <h1 class="project-title-main">{{ project.title }}</h1>

                    <p class="project-description-main">
                        {{ project.description || 'У проекта пока нет описания.' }}
                    </p>

                    <div class="project-stats">
                        <div class="project-stat">
                            <span class="stat-name">Всего задач</span>
                            <span class="stat-value">
                                {{
                                    columns.backlog.length +
                                    columns.in_progress.length +
                                    columns.review.length +
                                    columns.done.length
                                }}
                            </span>
                        </div>

                        <div class="project-stat">
                            <span class="stat-name">Backlog</span>
                            <span class="stat-value">{{ columns.backlog.length }}</span>
                        </div>

                        <div class="project-stat">
                            <span class="stat-name">In Progress</span>
                            <span class="stat-value">{{ columns.in_progress.length }}</span>
                        </div>

                        <div class="project-stat">
                            <span class="stat-name">Review</span>
                            <span class="stat-value">{{ columns.review.length }}</span>
                        </div>

                        <div class="project-stat">
                            <span class="stat-name">Done</span>
                            <span class="stat-value">{{ columns.done.length }}</span>
                        </div>
                    </div>
                </div>

                <div class="project-hero-right">
                    <Link :href="route('projects.index')" class="back-to-projects-btn">
                        ← К проектам
                    </Link>
                </div>
            </section>

            <div class="page">
                <div class="kanban">

            <!-- BACKLOG -->
            <div class="column">
                <h2>📌 Backlog <span>{{ columns.backlog.length }}</span></h2>

                <draggable
                    v-model="columns.backlog"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    class="drop-zone"
                >
                    <template #item="{ element: task }">
                        <div class="task" @click="openTaskDrawer(task)">
                            <div class="task-top">
                                <div :class="['task-title', { done: task.status === 'done' }]">
                                    {{ task.title }}
                                </div>

                                <span class="priority-badge">
                                    {{ priorityLabel(task.priority) }}
                                 </span>
                            </div>

                            <div v-if="task.description" class="task-preview">
                                {{ task.description }}
                            </div>

                            <div class="task-meta">
                                <span v-if="task.user">👤 {{ task.user.name }}</span>
                                <span v-if="task.due_date">📅 {{ formatShortDate(task.due_date) }}</span>
                            </div>

                            <div class="task-actions">
                                <!-- твои кнопки оставляй, но обязательно .stop -->
                                <button @click.stop="updateStatus(task.id, 'in_progress')">➡️ Start</button>
                                <button @click.stop="deleteTask(task.id)">🗑</button>
                            </div>
                        </div>
                    </template>

                    <template #footer>
                        <div v-if="columns.backlog.length === 0" class="empty-state">
                            No tasks here
                        </div>
                    </template>

                </draggable>

                <form @submit.prevent="submit" class="task-form">
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="New task..."
                    />
                    <button type="submit" :disabled="!form.title.trim()">
                        +
                    </button>
                </form>

            </div>

            <!-- IN PROGRESS -->
            <div class="column">
                <h2>⚙️ In Progress <span>{{ columns.in_progress.length }}</span></h2>

                <draggable
                    v-model="columns.in_progress"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    class="drop-zone"
                >
                    <template #item="{ element: task }">
                        <div class="task" @click="openTaskDrawer(task)">
                            <div class="task-top">
                                <div :class="['task-title', { done: task.status === 'done' }]">
                                    {{ task.title }}
                                </div>

                                <span class="priority-badge">
                                    {{ priorityLabel(task.priority) }}
                                 </span>
                            </div>

                            <div v-if="task.description" class="task-preview">
                                {{ task.description }}
                            </div>

                            <div class="task-meta">
                                <span v-if="task.user">👤 {{ task.user.name }}</span>
                                <span v-if="task.due_date">📅 {{ formatShortDate(task.due_date) }}</span>
                            </div>

                            <div class="task-actions">
                                <!-- твои кнопки оставляй, но обязательно .stop -->
                                <button @click.stop="updateStatus(task.id, 'in_progress')">➡️ Start</button>
                                <button @click.stop="deleteTask(task.id)">🗑</button>
                            </div>
                        </div>
                    </template>

                    <template #footer>
                        <div v-if="columns.in_progress.length === 0" class="empty-state">
                            No tasks here
                        </div>
                    </template>

                </draggable>
            </div>

            <!-- REVIEW -->
            <div class="column">
                <h2>🔍 Review <span>{{ columns.review.length }}</span></h2>

                <draggable
                    v-model="columns.review"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    class="drop-zone"
                >
                    <template #item="{ element: task }">
                        <div class="task" @click="openTaskDrawer(task)">
                            <div class="task-top">
                                <div :class="['task-title', { done: task.status === 'done' }]">
                                    {{ task.title }}
                                </div>

                                <span class="priority-badge">
                                    {{ priorityLabel(task.priority) }}
                                 </span>
                            </div>

                            <div v-if="task.description" class="task-preview">
                                {{ task.description }}
                            </div>

                            <div class="task-meta">
                                <span v-if="task.user">👤 {{ task.user.name }}</span>
                                <span v-if="task.due_date">📅 {{ formatShortDate(task.due_date) }}</span>
                            </div>

                            <div class="task-actions">
                                <!-- твои кнопки оставляй, но обязательно .stop -->
                                <button @click.stop="updateStatus(task.id, 'in_progress')">➡️ Start</button>
                                <button @click.stop="deleteTask(task.id)">🗑</button>
                            </div>
                        </div>
                    </template>

                    <template #footer>
                        <div v-if="columns.review.length === 0" class="empty-state">
                            No tasks here
                        </div>
                    </template>

                </draggable>

            </div>

            <!-- DONE -->
            <div class="column">
                <h2>🏁 Done <span>{{ columns.done.length }}</span></h2>

                <draggable
                    v-model="columns.done"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    class="drop-zone"
                >
                    <template #item="{ element: task }">
                        <div class="task" @click="openTaskDrawer(task)">
                            <div class="task-top">
                                <div :class="['task-title', { done: task.status === 'done' }]">
                                    {{ task.title }}
                                </div>

                                <span class="priority-badge">
                                    {{ priorityLabel(task.priority) }}
                                </span>
                            </div>

                            <div v-if="task.description" class="task-preview">
                                {{ task.description }}
                            </div>

                            <div class="task-meta">
                                <span v-if="task.user">👤 {{ task.user.name }}</span>
                                <span v-if="task.due_date">📅 {{ formatShortDate(task.due_date) }}</span>
                            </div>

                            <div class="task-actions">
                                <button @click.stop="updateStatus(task.id, 'in_progress')">➡️ Start</button>
                                <button @click.stop="deleteTask(task.id)">🗑</button>
                            </div>
                        </div>
                    </template>

                    <template #footer>
                        <div v-if="columns.done.length === 0" class="empty-state">
                            No tasks here
                        </div>
                    </template>

                </draggable>
            </div>

        </div>

        <div
            v-if="isDrawerOpen"
            class="drawer-overlay"
            @click="closeTaskDrawer"
        ></div>

        <aside class="task-drawer" :class="{ open: isDrawerOpen }">
            <div class="drawer-header">
                <h2>Карточка задачи</h2>
                <button class="drawer-close" @click="closeTaskDrawer">✕</button>
            </div>

            <form v-if="selectedTask" class="drawer-body" @submit.prevent="saveTaskDetails">
                <label class="drawer-field">
                    <span>Название</span>
                    <input v-model="drawerForm.title" type="text" />
                    <small v-if="drawerForm.errors.title" class="field-error">
                        {{ drawerForm.errors.title }}
                    </small>
                </label>

                <label class="drawer-field">
                    <span>Описание</span>
                    <textarea v-model="drawerForm.description" rows="5"></textarea>
                    <small v-if="drawerForm.errors.description" class="field-error">
                        {{ drawerForm.errors.description }}
                    </small>
                </label>

                <div class="drawer-grid">
                    <label class="drawer-field">
                        <span>Статус</span>
                        <select v-model="drawerForm.status">
                            <option value="backlog">Backlog</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="done">Done</option>
                        </select>
                    </label>

                    <label class="drawer-field">
                        <span>Приоритет</span>
                        <select v-model.number="drawerForm.priority">
                            <option :value="1">Низкий</option>
                            <option :value="2">Средний</option>
                            <option :value="3">Высокий</option>
                        </select>
                    </label>

                    <label class="drawer-field">
                        <span>Дедлайн</span>
                        <input v-model="drawerForm.due_date" type="datetime-local" />
                    </label>

                    <label class="drawer-field">
                        <span>Исполнитель</span>
                        <select v-model="drawerForm.user_id">
                            <option value="">Не назначен</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="drawer-meta">
                    <div><strong>Создана:</strong> {{ formatDateTime(selectedTask.created_at) }}</div>
                    <div><strong>Обновлена:</strong> {{ formatDateTime(selectedTask.updated_at) }}</div>
                </div>

                <div class="drawer-section">
                    <h3>Комментарии</h3>

                    <form class="comment-form" @submit.prevent="submitComment">
                        <textarea
                            v-model="commentForm.body"
                            rows="3"
                            placeholder="Напиши комментарий..."
                        ></textarea>

                        <small v-if="commentForm.errors.body" class="field-error">
                            {{ commentForm.errors.body }}
                        </small>

                        <button type="submit" :disabled="commentForm.processing">
                            Добавить комментарий
                        </button>
                    </form>

                    <div
                        v-if="selectedTask.comments && selectedTask.comments.length"
                        class="comments-list"
                    >
                        <div
                            v-for="comment in selectedTask.comments"
                            :key="comment.id"
                            class="comment-item"
                        >
                            <div class="comment-head">
                                <strong>{{ comment.user?.name ?? 'Пользователь' }}</strong>
                                <span>{{ formatDateTime(comment.created_at) }}</span>
                            </div>

                            <div class="comment-body">
                                {{ comment.body }}
                            </div>

                            <button
                                class="comment-delete"
                                @click="deleteComment(comment.id)"
                            >
                                Удалить
                            </button>
                        </div>
                    </div>

                    <div v-else class="drawer-placeholder">
                        Пока комментариев нет.
                    </div>
                </div>

                <div class="drawer-section">
                    <h3>Прикреплённые файлы</h3>

                    <form class="attachment-form" @submit.prevent="submitAttachment">
                        <input type="file" @change="onFileChange" />

                        <small v-if="attachmentForm.errors.file" class="field-error">
                            {{ attachmentForm.errors.file }}
                        </small>

                        <button type="submit" :disabled="attachmentForm.processing">
                            Загрузить файл
                        </button>
                    </form>

                    <div
                        v-if="selectedTask.attachments && selectedTask.attachments.length"
                        class="attachments-list"
                    >
                        <div
                            v-for="attachment in selectedTask.attachments"
                            :key="attachment.id"
                            class="attachment-item"
                        >
                            <div class="attachment-main">
                                <a :href="attachment.url" target="_blank">
                                    {{ attachment.original_name }}
                                </a>

                                <div class="attachment-meta">
                                    <span>{{ formatFileSize(attachment.size) }}</span>
                                    <span v-if="attachment.user">• {{ attachment.user.name }}</span>
                                    <span>• {{ formatDateTime(attachment.created_at) }}</span>
                                </div>
                            </div>

                            <button @click="deleteAttachment(attachment.id)">
                                Удалить
                            </button>
                        </div>
                    </div>

                    <div v-else class="drawer-placeholder">
                        Пока файлов нет.
                    </div>
                </div>

                <div class="drawer-footer">
                    <button type="submit" :disabled="drawerForm.processing">
                        Сохранить
                    </button>

                    <button
                        type="button"
                        class="danger-btn"
                        @click="deleteTask(selectedTask.id)"
                    >
                        Удалить задачу
                    </button>
                </div>
            </form>
        </aside>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.page {
    padding: 20px;
    font-family: sans-serif;
}

.title {
    font-size: 22px;
    margin-bottom: 20px;
}

.kanban {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.column {
    background: #f4f6f8;
    padding: 12px;
    border-radius: 10px;
    min-height: 300px;
}

.column h2 {
    font-size: 14px;
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.task {
    background: white;
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.task:hover {
    transform: translateY(-1px);
}

.task-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.task-title {
    font-size: 14px;
}

.task-title.done {
    text-decoration: line-through;
    opacity: 0.6;
}

.task-actions {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}

button {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    background: #e9ecef;
}

button:hover {
    background: #dce1e6;
}

.task-form {
    display: flex;
    gap: 6px;
    margin-top: 10px;
}

.task-form input {
    flex: 1;
    padding: 6px;
}

.back-btn {
    display: inline-block;
    margin: 20px 0 0 20px;
    font-size: 14px;
    color: #6b7280;
    text-decoration: none;
    transition: color 0.2s;
}
.back-btn:hover {
    color: #111827;
}

.drop-zone {
    min-height: 50px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.empty-state {
    padding: 15px;
    text-align: center;
    color: #9ca3af;
    font-size: 13px;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    background: transparent;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #e5e7eb;
}

.task {
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.task-preview {
    margin-top: 8px;
    font-size: 13px;
    color: #6b7280;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.task-meta {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 12px;
    color: #6b7280;
}

.priority-badge {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    white-space: nowrap;
}

.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.35);
    z-index: 40;
}

.task-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: 420px;
    max-width: 100%;
    height: 100vh;
    background: #fff;
    box-shadow: -8px 0 24px rgba(0, 0, 0, 0.12);
    transform: translateX(100%);
    transition: transform 0.25s ease;
    z-index: 50;
    display: flex;
    flex-direction: column;
}

.task-drawer.open {
    transform: translateX(0);
}

.drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.drawer-header h2 {
    margin: 0;
    font-size: 18px;
}

.drawer-close {
    background: transparent;
    font-size: 18px;
    padding: 0;
}

.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.drawer-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.drawer-field input,
.drawer-field textarea,
.drawer-field select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
}

.drawer-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.drawer-meta {
    display: grid;
    gap: 6px;
    padding: 12px;
    border-radius: 10px;
    background: #f9fafb;
    font-size: 13px;
    color: #4b5563;
}

.drawer-section h3 {
    margin: 0 0 8px 0;
    font-size: 15px;
}

.drawer-placeholder {
    padding: 12px;
    border: 1px dashed #d1d5db;
    border-radius: 10px;
    background: #f9fafb;
    color: #6b7280;
    font-size: 13px;
}

.drawer-footer {
    display: flex;
    gap: 10px;
    margin-top: auto;
}

.danger-btn {
    background: #fee2e2;
    color: #991b1b;
}

.field-error {
    color: #dc2626;
    font-size: 12px;
}

@media (max-width: 900px) {
    .kanban {
        grid-template-columns: 1fr 1fr;
    }

    .drawer-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .kanban {
        grid-template-columns: 1fr;
    }

    .task-drawer {
        width: 100%;
    }
}

.comment-form,
.attachment-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
}

.comments-list,
.attachments-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.comment-item,
.attachment-item {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px;
    background: #fff;
}

.comment-head {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 8px;
}

.comment-body {
    white-space: pre-wrap;
    color: #111827;
    font-size: 14px;
}

.comment-delete {
    margin-top: 8px;
    background: #fee2e2;
    color: #991b1b;
}

.attachment-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}

.attachment-main a {
    font-weight: 600;
    color: #2563eb;
    text-decoration: none;
}

.attachment-meta {
    margin-top: 6px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    font-size: 12px;
    color: #6b7280;
}

.project-show-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.project-hero {
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    color: white;
    border-radius: 20px;
    padding: 28px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
}

.project-hero-left {
    flex: 1;
    min-width: 0;
}

.breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    font-size: 14px;
    margin-bottom: 14px;
    color: rgba(255, 255, 255, 0.8);
}

.breadcrumbs a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
}

.breadcrumbs .current {
    color: white;
    font-weight: 600;
}

.project-title-main {
    margin: 0;
    font-size: 34px;
    font-weight: 800;
}

.project-description-main {
    margin: 12px 0 0 0;
    max-width: 780px;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.92);
}

.project-stats {
    margin-top: 22px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.project-stat {
    min-width: 110px;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stat-name {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8);
}

.stat-value {
    font-size: 22px;
    font-weight: 800;
    color: white;
}

.back-to-projects-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.16);
    color: white;
    padding: 12px 16px;
    border-radius: 12px;
    font-weight: 700;
    white-space: nowrap;
}

@media (max-width: 900px) {
    .project-hero {
        flex-direction: column;
    }

    .project-title-main {
        font-size: 28px;
    }
}
</style>
