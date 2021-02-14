<template>
    <div>
        <div v-if="userPermissions.canAddCompanyMembers">
            <jet-section-border />

            <!-- Add Company Member -->
            <jet-form-section @submitted="addCompanyMember">
                <template #title>
                    Add Company Member
                </template>

                <template #description>
                    Add a new company member to your company, allowing them to collaborate with you.
                </template>

                <template #form>
                    <div class="col-span-6">
                        <div class="max-w-xl text-sm text-gray-600">
                            Please provide the email address of the person you would like to add to this company.
                        </div>
                    </div>

                    <!-- Member Email -->
                    <div class="col-span-6 sm:col-span-4">
                        <jet-label for="email" value="Email" />
                        <jet-input id="email" type="email" class="mt-1 block w-full" v-model="addCompanyMemberForm.email" />
                        <jet-input-error :message="addCompanyMemberForm.errors.email" class="mt-2" />
                    </div>

                    <!-- Role -->
                    <div class="col-span-6 lg:col-span-4" v-if="availableRoles.length > 0">
                        <jet-label for="roles" value="Role" />
                        <jet-input-error :message="addCompanyMemberForm.errors.role" class="mt-2" />

                        <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
                            <button type="button" class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
                                            :class="{'border-t border-gray-200 rounded-t-none': i > 0, 'rounded-b-none': i != Object.keys(availableRoles).length - 1}"
                                            @click="addCompanyMemberForm.role = role.key"
                                            v-for="(role, i) in availableRoles"
                                            :key="role.key">
                                <div :class="{'opacity-50': addCompanyMemberForm.role && addCompanyMemberForm.role != role.key}">
                                    <!-- Role Name -->
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-600" :class="{'font-semibold': addCompanyMemberForm.role == role.key}">
                                            {{ role.name }}
                                        </div>

                                        <svg v-if="addCompanyMemberForm.role == role.key" class="ml-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>

                                    <!-- Role Description -->
                                    <div class="mt-2 text-xs text-gray-600">
                                        {{ role.description }}
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </template>

                <template #actions>
                    <jet-action-message :on="addCompanyMemberForm.recentlySuccessful" class="mr-3">
                        Added.
                    </jet-action-message>

                    <jet-button :class="{ 'opacity-25': addCompanyMemberForm.processing }" :disabled="addCompanyMemberForm.processing">
                        Add
                    </jet-button>
                </template>
            </jet-form-section>
        </div>

        <div v-if="company.company_invitations.length > 0 && userPermissions.canAddCompanyMembers">
            <jet-section-border />

            <!-- Company Member Invitations -->
            <jet-action-section class="mt-10 sm:mt-0">
                <template #title>
                    Pending Company Invitations
                </template>

                <template #description>
                    These people have been invited to your company and have been sent an invitation email. They may join the company by accepting the email invitation.
                </template>

                <!-- Pending Company Member Invitation List -->
                <template #content>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between" v-for="invitation in company.company_invitations" :key="invitation.id">
                            <div class="text-gray-600">{{ invitation.email }}</div>

                            <div class="flex items-center">
                                <!-- Cancel Company Invitation -->
                                <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none"
                                                    @click="cancelCompanyInvitation(invitation)"
                                                    v-if="userPermissions.canRemoveCompanyMembers">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </jet-action-section>
        </div>

        <div v-if="company.users.length > 0">
            <jet-section-border />

            <!-- Manage Company Members -->
            <jet-action-section class="mt-10 sm:mt-0">
                <template #title>
                    Company Members
                </template>

                <template #description>
                    All of the people that are part of this company.
                </template>

                <!-- Company Member List -->
                <template #content>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between" v-for="user in company.users" :key="user.id">
                            <div class="flex items-center">
                                <img class="w-8 h-8 rounded-full" :src="user.profile_photo_url" :alt="user.name">
                                <div class="ml-4">{{ user.name }}</div>
                            </div>

                            <div class="flex items-center">
                                <!-- Manage Company Member Role -->
                                <button class="ml-2 text-sm text-gray-400 underline"
                                        @click="manageRole(user)"
                                        v-if="userPermissions.canAddCompanyMembers && availableRoles.length">
                                    {{ displayableRole(user.membership.role) }}
                                </button>

                                <div class="ml-2 text-sm text-gray-400" v-else-if="availableRoles.length">
                                    {{ displayableRole(user.membership.role) }}
                                </div>

                                <!-- Leave Company -->
                                <button class="cursor-pointer ml-6 text-sm text-red-500"
                                                    @click="confirmLeavingCompany"
                                                    v-if="$page.props.user.id === user.id">
                                    Leave
                                </button>

                                <!-- Remove Company Member -->
                                <button class="cursor-pointer ml-6 text-sm text-red-500"
                                                    @click="confirmCompanyMemberRemoval(user)"
                                                    v-if="userPermissions.canRemoveCompanyMembers">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </jet-action-section>
        </div>

        <!-- Role Management Modal -->
        <jet-dialog-modal :show="currentlyManagingRole" @close="currentlyManagingRole = false">
            <template #title>
                Manage Role
            </template>

            <template #content>
                <div v-if="managingRoleFor">
                    <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
                        <button type="button" class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
                                        :class="{'border-t border-gray-200 rounded-t-none': i > 0, 'rounded-b-none': i !== Object.keys(availableRoles).length - 1}"
                                        @click="updateRoleForm.role = role.key"
                                        v-for="(role, i) in availableRoles"
                                        :key="role.key">
                            <div :class="{'opacity-50': updateRoleForm.role && updateRoleForm.role !== role.key}">
                                <!-- Role Name -->
                                <div class="flex items-center">
                                    <div class="text-sm text-gray-600" :class="{'font-semibold': updateRoleForm.role === role.key}">
                                        {{ role.name }}
                                    </div>

                                    <svg v-if="updateRoleForm.role === role.key" class="ml-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>

                                <!-- Role Description -->
                                <div class="mt-2 text-xs text-gray-600">
                                    {{ role.description }}
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </template>

            <template #footer>
                <jet-secondary-button @click.native="currentlyManagingRole = false">
                    Nevermind
                </jet-secondary-button>

                <jet-button class="ml-2" @click.native="updateRole" :class="{ 'opacity-25': updateRoleForm.processing }" :disabled="updateRoleForm.processing">
                    Save
                </jet-button>
            </template>
        </jet-dialog-modal>

        <!-- Leave Company Confirmation Modal -->
        <jet-confirmation-modal :show="confirmingLeavingCompany" @close="confirmingLeavingCompany = false">
            <template #title>
                Leave Company
            </template>

            <template #content>
                Are you sure you would like to leave this company?
            </template>

            <template #footer>
                <jet-secondary-button @click.native="confirmingLeavingCompany = false">
                    Nevermind
                </jet-secondary-button>

                <jet-danger-button class="ml-2" @click.native="leaveCompany" :class="{ 'opacity-25': leaveCompanyForm.processing }" :disabled="leaveCompanyForm.processing">
                    Leave
                </jet-danger-button>
            </template>
        </jet-confirmation-modal>

        <!-- Remove Company Member Confirmation Modal -->
        <jet-confirmation-modal :show="companyMemberBeingRemoved" @close="companyMemberBeingRemoved = null">
            <template #title>
                Remove Company Member
            </template>

            <template #content>
                Are you sure you would like to remove this person from the company?
            </template>

            <template #footer>
                <jet-secondary-button @click.native="companyMemberBeingRemoved = null">
                    Nevermind
                </jet-secondary-button>

                <jet-danger-button class="ml-2" @click.native="removeCompanyMember" :class="{ 'opacity-25': removeCompanyMemberForm.processing }" :disabled="removeCompanyMemberForm.processing">
                    Remove
                </jet-danger-button>
            </template>
        </jet-confirmation-modal>
    </div>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetActionSection from '@/Jetstream/ActionSection'
    import JetButton from '@/Jetstream/Button'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
    import JetDangerButton from '@/Jetstream/DangerButton'
    import JetDialogModal from '@/Jetstream/DialogModal'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'
    import JetSectionBorder from '@/Jetstream/SectionBorder'

    export default {
        components: {
            JetActionMessage,
            JetActionSection,
            JetButton,
            JetConfirmationModal,
            JetDangerButton,
            JetDialogModal,
            JetFormSection,
            JetInput,
            JetInputError,
            JetLabel,
            JetSecondaryButton,
            JetSectionBorder,
        },

        props: [
            'company',
            'availableRoles',
            'userPermissions'
        ],

        data() {
            return {
                addCompanyMemberForm: this.$inertia.form({
                    email: '',
                    role: null,
                }),

                updateRoleForm: this.$inertia.form({
                    role: null,
                }),

                leaveCompanyForm: this.$inertia.form(),
                removeCompanyMemberForm: this.$inertia.form(),

                currentlyManagingRole: false,
                managingRoleFor: null,
                confirmingLeavingCompany: false,
                companyMemberBeingRemoved: null,
            }
        },

        methods: {
            addCompanyMember() {
                this.addCompanyMemberForm.post(route('company-members.store', this.company), {
                    errorBag: 'addCompanyMember',
                    preserveScroll: true,
                    onSuccess: () => this.addCompanyMemberForm.reset(),
                });
            },

            cancelCompanyInvitation(invitation) {
                this.$inertia.delete(route('company-invitations.destroy', invitation), {
                    preserveScroll: true
                });
            },

            manageRole(companyMember) {
                this.managingRoleFor = companyMember
                this.updateRoleForm.role = companyMember.membership.role
                this.currentlyManagingRole = true
            },

            updateRole() {
                this.updateRoleForm.put(route('company-members.update', [this.company, this.managingRoleFor]), {
                    preserveScroll: true,
                    onSuccess: () => (this.currentlyManagingRole = false),
                })
            },

            confirmLeavingCompany() {
                this.confirmingLeavingCompany = true
            },

            leaveCompany() {
                this.leaveCompanyForm.delete(route('company-members.destroy', [this.company, this.$page.props.user]))
            },

            confirmCompanyMemberRemoval(companyMember) {
                this.companyMemberBeingRemoved = companyMember
            },

            removeCompanyMember() {
                this.removeCompanyMemberForm.delete(route('company-members.destroy', [this.company, this.companyMemberBeingRemoved]), {
                    errorBag: 'removeCompanyMember',
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => (this.companyMemberBeingRemoved = null),
                })
            },

            displayableRole(role) {
                return this.availableRoles.find(r => r.key === role).name
            },
        },
    }
</script>
