<script setup>
import { useForm } from '@inertiajs/vue3'

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
</script>

<template>
    <div>
        <h1>Projects</h1>

        <!-- ФОРМА -->
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

        <!-- СПИСОК -->
        <ul>
            <li v-for="project in projects" :key="project.id">
                {{ project.title }}
            </li>
        </ul>
    </div>
</template>

<style>
</style>
