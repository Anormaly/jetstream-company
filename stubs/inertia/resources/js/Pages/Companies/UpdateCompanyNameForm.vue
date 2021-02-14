<template>
    <jet-form-section @submitted="updateCompanyName">
        <template #title>
            Company Name
        </template>

        <template #description>
            The company's name and owner information.
        </template>

        <template #form>
            <!-- Company Owner Information -->
            <div class="col-span-6">
                <jet-label value="Company Owner" />

                <div class="flex items-center mt-2">
                    <img class="w-12 h-12 rounded-full object-cover" :src="company.owner.profile_photo_url" :alt="company.owner.name">

                    <div class="ml-4 leading-tight">
                        <div>{{ company.owner.name }}</div>
                        <div class="text-gray-700 text-sm">{{ company.owner.email }}</div>
                    </div>
                </div>
            </div>

            <!-- Company Name -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="name" value="Company Name" />

                <jet-input id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            :disabled="! permissions.canUpdateCompany" />

                <jet-input-error :message="form.errors.name" class="mt-2" />
            </div>
        </template>

        <template #actions v-if="permissions.canUpdateCompany">
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </jet-action-message>

            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </jet-button>
        </template>
    </jet-form-section>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetButton from '@/Jetstream/Button'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'

    export default {
        components: {
            JetActionMessage,
            JetButton,
            JetFormSection,
            JetInput,
            JetInputError,
            JetLabel,
        },

        props: ['company', 'permissions'],

        data() {
            return {
                form: this.$inertia.form({
                    name: this.company.name,
                })
            }
        },

        methods: {
            updateCompanyName() {
                this.form.put(route('companies.update', this.company), {
                    errorBag: 'updateCompanyName',
                    preserveScroll: true
                });
            },
        },
    }
</script>
