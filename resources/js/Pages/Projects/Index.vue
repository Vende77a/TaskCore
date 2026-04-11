<script setup>
import { useForm, router } from '@inertiajs/vue3'

defineProps({
    projects: Array
})

const form = useForm({
    title: '',
    description: ''
})

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => form.reset()
    })
}

const deleteProject = (projectId) => {
    if (confirm('Удалить проект?')) {
        router.delete(route('projects.destroy', projectId), {
            preserveScroll: true
        })
    }
}
</script>

<template>
    <div>
        <h1>Projects</h1>

        <form @submit.prevent="submit">
            <div>
                <input
                    v-model="form.title"
                    type="text"
                    placeholder="Название проекта"
                />

                <div v-if="form.errors.title">
                    {{ form.errors.title }}
                </div>
            </div>

            <div>
                <textarea
                    v-model="form.description"
                    placeholder="Описание"
                ></textarea>

                <div v-if="form.errors.description">
                    {{ form.errors.description }}
                </div>
            </div>

            <button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Создание...' : 'Создать' }}
            </button>
        </form>

        <hr>

        <ul>
            <li
                v-for="project in projects"
                :key="project.id"
                class="project-item"
            >
        <span
            class="project-title"
            @click="router.get(route('projects.show', project.id))"
        >
            {{ project.title }}
        </span>

                <button
                    class="delete-btn"
                    @click="deleteProject(project.id)"
                >
                    🗑️
                </button>
            </li>
        </ul>
    </div>
</template>

<style>
.project-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    margin-bottom: 6px;
    background: #f5f5f5;
    border-radius: 6px;
}

.project-title {
    cursor: pointer;
}

.project-title:hover {
    text-decoration: underline;
}

.delete-btn {
    background: transparent;
    border: none;
    cursor: pointer;
}
</style>
