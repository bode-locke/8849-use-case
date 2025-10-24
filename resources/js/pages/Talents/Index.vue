<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { ref } from 'vue';

type PaginatorLink = { url: string | null; label: string; active: boolean };

interface Talent {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    role: string;
    created_at: string;
    ayon_sync_status: string;
}

interface PaginatedTalents {
    data: Talent[];
    links: PaginatorLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{ talents: PaginatedTalents }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: dashboard().url }];

const showDeleteModal = ref(false);
const selectedTalent = ref<Talent | null>(null);

function confirmDestroyTalent(talent: Talent) {
    selectedTalent.value = talent;
    showDeleteModal.value = true;
}

function deactivateTalent() {
    if (!selectedTalent.value) return;

    router.put(`/talents/${selectedTalent.value.id}`, { ayon_sync_status: 'inactive' }, {
        preserveScroll: true,
        onFinish: () => (showDeleteModal.value = false),
    });
}

function destroyTalent() {
    if (!selectedTalent.value) return;

    router.delete(`/talents/${selectedTalent.value.id}`, {
        preserveScroll: true,
        onFinish: () => (showDeleteModal.value = false),
    });
}
function syncTalent(talent: Talent) {
    router.visit(`/talents/${talent.id}`, {
        method: 'put',
        data: { ayon_sync_status: 'synced' },
        preserveScroll: true,
    });
}

</script>

<template>
    <Head title="Talents" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Talents</h1>
                <Link href="/talents/create">
                    <Button size="sm">New Talent</Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="min-w-full divide-y divide-sidebar-border/70 text-sm dark:divide-sidebar-border">
                    <div class="grid grid-cols-6 bg-neutral-50 px-4 py-2 font-medium dark:bg-neutral-900">
                        <div>Name</div>
                        <div>Email</div>
                        <div>Role</div>
                        <div>Ayon Status</div>
                        <div>Created</div>
                        <div class="text-right">Actions</div>
                    </div>

                    <div v-if="props.talents.data.length === 0" class="px-4 py-6 text-neutral-500">
                        No talents yet.
                    </div>

                    <div v-else>
                        <div
                            v-for="talent in props.talents.data"
                            :key="talent.id"
                            class="grid grid-cols-6 items-center px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-900/50"
                        >
                            <div>{{ talent.first_name }} {{ talent.last_name }}</div>
                            <div class="truncate">{{ talent.email }}</div>
                            <div class="uppercase">{{ talent.role }}</div>
                            <div>
                                <span
                                    class="capitalize inline-flex items-center rounded-md px-2 py-1 text-xs font-medium inset-ring"
                                    :class="{
                                        'bg-gray-400/10 text-gray-400 inset-ring-gray-400/20': talent.ayon_sync_status === 'inactive',
                                        'bg-green-400/10 text-green-400 inset-ring-green-500/20': talent.ayon_sync_status === 'synced',
                                        'bg-yellow-400/10 text-yellow-400 inset-ring-yellow-400/20': talent.ayon_sync_status === 'pending',
                                        'bg-red-400/10 text-red-400 inset-ring-red-400/20': talent.ayon_sync_status === 'error'
                                    }"
                                >
                                    {{ talent.ayon_sync_status }}
                                </span>
                            </div>
                            <div class="text-neutral-500">{{ new Date(talent.created_at).toLocaleDateString() }}</div>
                            <div class="flex items-center justify-end gap-2">
                                <Button
                                    v-if="talent.ayon_sync_status === 'pending' || talent.ayon_sync_status === 'inactive'"
                                    size="sm"
                                    variant="secondary"
                                    @click="syncTalent(talent)"
                                >
                                    Sync
                                </Button>

                                <Link :href="`/talents/${talent.id}/edit`">
                                    <Button size="sm" variant="outline">Edit</Button>
                                </Link>
                                <Button size="sm" variant="destructive" @click="confirmDestroyTalent(talent)">
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-4 space-x-2">
            <div class="flex justify-center items-center mt-6 gap-2">
                <template v-for="link in props.talents.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        @click.prevent="router.visit(link.url)"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        v-html="link.label"
                        class="min-w-[36px]"
                    />
                    <Button
                        v-else
                        disabled
                        size="sm"
                        variant="ghost"
                        v-html="link.label"
                        class="min-w-[36px] opacity-50 cursor-not-allowed"
                    />
                </template>
            </div>

        </div>

        </div>

        <!-- MODAL -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div class="bg-white dark:bg-neutral-900 rounded-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Confirm Action</h2>
                <p class="mb-6">
                    What do you want to do with
                    <strong>{{ selectedTalent?.first_name }} {{ selectedTalent?.last_name }}</strong>?
                </p>
                <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="showDeleteModal = false">Cancel</Button>
                    <Button variant="secondary" @click="deactivateTalent">Deactivate</Button>
                    <Button variant="destructive" @click="destroyTalent">Delete</Button>
                </div>
            </div>
        </div>
    </AppLayout>

</template>
