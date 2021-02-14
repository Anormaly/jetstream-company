<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Company Settings
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <update-company-name-form :company="company" :permissions="permissions" />

                <company-member-manager class="mt-10 sm:mt-0"
                            :company="company"
                            :available-roles="availableRoles"
                            :user-permissions="permissions" />

                <template v-if="permissions.canDeleteCompany">
                    <jet-section-border />

                    <delete-company-form class="mt-10 sm:mt-0" :company="company" />
                </template>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import CompanyMemberManager from './CompanyMemberManager'
    import AppLayout from '@/Layouts/AppLayout'
    import DeleteCompanyForm from './DeleteCompanyForm'
    import JetSectionBorder from '@/Jetstream/SectionBorder'
    import UpdateCompanyNameForm from './UpdateCompanyNameForm'

    export default {
        props: [
            'company',
            'availableRoles',
            'permissions',
        ],

        components: {
            AppLayout,
            DeleteCompanyForm,
            JetSectionBorder,
            CompanyMemberManager,
            UpdateCompanyNameForm,
        },
    }
</script>
