<script setup>
import { useForm, router, Link } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
    project: Object
})

const editingTaskId = ref(null)
const editingTitle = ref('')

// 1. Создаем локальные изменяемые массивы, чтобы vuedraggable мог перекидывать между ними задачи
const columns = ref({
    backlog: [],
    in_progress: [],
    review: [],
    done: []
})

// 2. Функция для распределения задач с учетом их сортировки
const syncColumns = () => {
    const tasks = props.project.tasks;
    columns.value.backlog = tasks.filter(t => t.status === 'backlog').sort((a, b) => a.order - b.order)
    columns.value.in_progress = tasks.filter(t => t.status === 'in_progress').sort((a, b) => a.order - b.order)
    columns.value.review = tasks.filter(t => t.status === 'review').sort((a, b) => a.order - b.order)
    columns.value.done = tasks.filter(t => t.status === 'done').sort((a, b) => a.order - b.order)
}

// Заполняем колонки при первой загрузке
syncColumns()

// Обновляем колонки, если данные пришли с сервера
watch(() => props.project.tasks, syncColumns, { deep: true })

const deleteTask = (taskId) => {
    if (confirm('Удалить задачу?')) {
        router.delete(route('tasks.destroy', taskId))
    }
}

const form = useForm({
    title: '',
    project_id: props.project.id,
    priority: 1
})

const submit = () => {
    form.post(route('tasks.store'), {
        onSuccess: () => form.reset('title')
    })
}

const updateStatus = (taskId, newStatus) => {
    router.patch(route('tasks.update', taskId), {
        status: newStatus
    })
}

const startEdit = (task) => {
    editingTaskId.value = task.id
    editingTitle.value = task.title
}

const saveEdit = (taskId) => {
    router.patch(route('tasks.update', taskId), {
        title: editingTitle.value
    })
    editingTaskId.value = null
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
</script>

<template>

    <Link :href="route('projects.index')" class="back-btn">
        ← Back to projects
    </Link>

    <div class="page">
        <h1 class="title">{{ project.title }}</h1>

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

                        <div class="task">

                            <div class="task-top">
                                <div v-if="editingTaskId === task.id">
                                    <input
                                        class="edit-input"
                                        v-model="editingTitle"
                                        @keyup.enter="saveEdit(task.id)"
                                        @blur="saveEdit(task.id)"
                                        autofocus
                                    />
                                </div>

                                <div v-else class="task-title">
                                    {{ task.title }}
                                </div>

                                <button class="icon-btn" @click="startEdit(task)">✏️</button>
                            </div>

                            <div class="task-actions">
                                <button @click="updateStatus(task.id, 'in_progress')">
                                    ➡️ Start
                                </button>
                                <button @click="deleteTask(task.id)">🗑️</button>
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



                        <div class = "task">
                            <div class="task-top">
                                <div v-if="editingTaskId === task.id">
                                    <input
                                        class="edit-input"
                                        v-model="editingTitle"
                                        @keyup.enter="saveEdit(task.id)"
                                        @blur="saveEdit(task.id)"
                                    />
                                </div>

                                <div v-else class="task-title">
                                    {{ task.title }}
                                </div>

                                <button class="icon-btn" @click="startEdit(task)">✏️</button>
                            </div>


                            <div class="task-actions">
                                <button @click="updateStatus(task.id, 'review')">
                                    Review
                                </button>
                                <button @click="deleteTask(task.id)">🗑️</button>
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



                        <div class="task">

                            <div class="task-top">
                                <div v-if="editingTaskId === task.id">
                                    <input
                                        class="edit-input"
                                        v-model="editingTitle"
                                        @keyup.enter="saveEdit(task.id)"
                                        @blur="saveEdit(task.id)"
                                    />
                                </div>

                                <div v-else class="task-title">
                                    {{ task.title }}
                                </div>

                                <button class="icon-btn" @click="startEdit(task)">✏️</button>
                            </div>

                            <div class="task-actions">
                                <button @click="updateStatus(task.id, 'done')">
                                    ✅ Done
                                </button>
                                <button @click="updateStatus(task.id, 'in_progress')">
                                    ↩ Back
                                </button>
                                <button @click="deleteTask(task.id)">🗑️</button>
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



                        <div class = "task">

                            <div class="task-top">
                                <div v-if="editingTaskId === task.id">
                                    <input
                                        class="edit-input"
                                        v-model="editingTitle"
                                        @keyup.enter="saveEdit(task.id)"
                                        @blur="saveEdit(task.id)"
                                    />
                                </div>

                                <div v-else class="task-title done">
                                    {{ task.title }}
                                </div>

                                <button class="icon-btn" @click="startEdit(task)">✏️</button>
                            </div>

                            <div class="task-actions">
                                <button @click="deleteTask(task.id)">🗑️</button>
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
    </div>
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

.icon-btn {
    background: transparent;
    font-size: 14px;
}

.edit-input {
    width: 100%;
    padding: 4px;
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
</style>
