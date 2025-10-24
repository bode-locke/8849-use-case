<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import InputError from '@/components/InputError.vue';

interface Talent {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    role: string;
}

interface Role {
    value: string;
    label: string;
}

interface Props {
    talent: Talent;
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Talents', href: '/talents' },
    { title: 'Edit', href: `/talents/${props.talent.id}/edit` },
];

const form = useForm({
    first_name: props.talent.first_name,
    last_name: props.talent.last_name,
    email: props.talent.email,
    role: props.talent.role,
});

function submit() {
    form.put(`/talents/${props.talent.id}`);
}
</script>

<template>
    <Head title="Edit Talent" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Edit Talent</h1>
                <Link href="/talents">
                    <Button size="sm" variant="outline">Back to Talents</Button>
                </Link>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.first_name" class="mt-2" />
                        </div>

                        <div>
                            <Label for="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.last_name" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div>
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="">Select a role</option>
                            <option v-for="role in props.roles" :key="role.value" :value="role.value">
                                {{ role.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Talent' }}
                        </Button>
                        <Link href="/talents">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
