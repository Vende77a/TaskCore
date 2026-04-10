<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    project: Object
})

const backlogTasks = computed(() =>
    props.project.tasks.filter(t => t.status === 'backlog')
)
const inProgressTasks = computed(() =>
    props.project.tasks.filter(t => t.status === 'in_progress')
)
const ReviewTasks = computed(() =>
    props.project.tasks.filter(t => t.status === 'review')
)
const doneTasks = computed(() =>
    props.project.tasks.filter(t => t.status === 'done')
)

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
</script>

<template>
    <h1>{{ project.title }}</h1>

    <div class="kanban">

        <div class="column">
            <h2>Backlog</h2>

            <div v-for="task in backlogTasks" :key="task.id" class="task">
                {{ task.title }}
                <button @click="updateStatus(task.id, 'in_progress')">➡️ В работу</button>
            </div>

            <form @submit.prevent="submit">
                <input
                    v-model="form.title"
                    type="text"
                    placeholder="Новая задача"
                />

                <button type="submit" :disabled="form.processing">
                    +
                </button>

                <div v-if="form.errors.title">
                    {{ form.errors.title }}
                </div>
            </form>

        </div>

        <div class="column">
            <h2>In Progress</h2>

            <div v-for="task in inProgressTasks" :key="task.id" class="task">
                {{ task.title }}
                <button @click="updateStatus(task.id, 'review')">На проверку</button>
            </div>

        </div>

        <div class="column">
            <h2>Review</h2>

            <div v-for="task in ReviewTasks" :key="task.id" class="task">
                {{ task.title }}
                <button @click="updateStatus(task.id, 'done')">✅ Готово</button>
                <button @click="updateStatus(task.id, 'in_progress')">          ↩ Вернуть в работу</button>
            </div>

        </div>

        <div class="column">
            <h2>Done</h2>

            <div
                v-for="task in doneTasks"
            >
                {{ task.title }}
            </div>

        </div>

    </div>

</template>

<style scoped>
.kanban {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.column {
    flex: 1;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 8px;
}

.task {
    background: white;
    padding: 8px;
    margin-bottom: 8px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>
