<template>
    <jet-action-section>
        <template #title>
            Delete Company
        </template>

        <template #description>
            Permanently delete this company.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Once a company is deleted, all of its resources and data will be permanently deleted. Before deleting this company, please download any data or information regarding this company that you wish to retain.
            </div>

            <div class="mt-5">
                <jet-danger-button @click="confirmCompanyDeletion">
                    Delete Company
                </jet-danger-button>
            </div>

            <!-- Delete Company Confirmation Modal -->
            <jet-confirmation-modal :show="confirmingCompanyDeletion" @close="confirmingCompanyDeletion = false">
                <template #title>
                    Delete Company

                </template>

                <template #content>
                    Are you sure you want to delete this company? Once a company is deleted, all of its resources and data will be permanently deleted.
                </template>

                <template #footer>
                    <jet-secondary-button @click="confirmingCompanyDeletion = false">
                        Cancel
                    </jet-secondary-button>

                    <jet-danger-button class="ml-2" @click="deleteCompany" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Delete Company
                    </jet-danger-button>
                </template>
            </jet-confirmation-modal>
        </template>
    </jet-action-section>
</template>

<script>
    import JetActionSection from '@/Jetstream/ActionSection'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
    import JetDangerButton from '@/Jetstream/DangerButton'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'

    export default {
        props: ['company'],

        components: {
            JetActionSection,
            JetConfirmationModal,
            JetDangerButton,
            JetSecondaryButton,
        },

        data() {
            return {
                confirmingCompanyDeletion: false,
                deleting: false,

                form: this.$inertia.form()
            }
        },

        methods: {
            confirmCompanyDeletion() {
                this.confirmingCompanyDeletion = true
            },

            deleteCompany() {
                this.form.delete(route('companies.destroy', this.company), {
                    errorBag: 'deleteCompany'
                });
            },
        },
    }
</script>
