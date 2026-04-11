<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'

const editingTaskId = ref(null)
const editingTitle = ref('')

const props = defineProps({
    project: Object
})

const localTasks = ref([...props.project.tasks])

watch(() => props.project.tasks, (newTasks) => {
    localTasks.value = [...newTasks]
}, { deep: true })

const backlogTasks = computed({
    get: () => localTasks.value.filter(t => t.status === 'backlog'),
    set: (val) => updateLocalOrder(val, 'backlog')
})
const inProgressTasks = computed({
    get: () => localTasks.value.filter(t => t.status === 'in_progress'),
    set: (val) => updateLocalOrder(val, 'in_progress')
})
const reviewTasks = computed({
    get: () => localTasks.value.filter(t => t.status === 'review'),
    set: (val) => updateLocalOrder(val, 'review')
})
const doneTasks = computed({
    get: () => localTasks.value.filter(t => t.status === 'done'),
    set: (val) => updateLocalOrder(val, 'done')
})

const updateLocalOrder = (filteredTasks, status) => {

    const otherTasks = localTasks.value.filter(t => t.status !== status)
    localTasks.value = [...otherTasks, ...filteredTasks]
}

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

const onChange = (event, newStatus) => {
    const movedItem = event.added?.element

    if (!movedItem) return

    router.patch(route('tasks.update', movedItem.id), {
        status: newStatus
    })
}

const onEnd = () => {
    // Собираем ID прямо из локального реактивного массива,
    // который vuedraggable уже отсортировал
    const ids = localTasks.value.map(t => t.id);

    console.log("Отправляем ID:", ids);

    router.post(route('tasks.reorder'), {
        tasks: ids
    }, {
        preserveScroll: true,
    });

}
</script>

<template>
    <div class="page">
        <h1 class="title">{{ project.title }}</h1>

        <div class="kanban">

            <!-- BACKLOG -->
            <div class="column">
                <h2>📌 Backlog <span>{{ backlogTasks.length }}</span></h2>

                <draggable
                    v-model="backlogTasks"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    @change="(e) => onChange(e, 'backlog')"
                >
                    <template #item="{ element: task }">

                        <div class="task" :data-id="task.id">

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
                </draggable>

                <form @submit.prevent="submit" class="task-form">
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="New task..."
                    />
                    <button type="submit">+</button>
                </form>
            </div>

            <!-- IN PROGRESS -->
            <div class="column">
                <h2>⚙️ In Progress <span>{{ inProgressTasks.length }}</span></h2>

                <draggable
                    v-model="inProgressTasks"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    @change="(e) => onChange(e, 'in_progress')"
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
                </draggable>
            </div>

            <!-- REVIEW -->
            <div class="column">
                <h2>🔍 Review <span>{{ reviewTasks.length }}</span></h2>

                <draggable
                    v-model="reviewTasks"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    @change="(e) => onChange(e, 'review')"
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
                </draggable>

            </div>

            <!-- DONE -->
            <div class="column">
                <h2>🏁 Done <span>{{ doneTasks.length }}</span></h2>

                <draggable
                    v-model="doneTasks"
                    group="tasks"
                    item-key="id"
                    @end="onEnd"
                    @change="(e) => onChange(e, 'done')"
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
</style>
