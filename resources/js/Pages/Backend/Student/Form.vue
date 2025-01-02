

<script setup>
    import { ref, onMounted } from 'vue';
    import BackendLayout from '@/Layouts/BackendLayout.vue';
    import { router, useForm, usePage } from '@inertiajs/vue3';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AlertMessage from '@/Components/AlertMessage.vue';
    import { displayResponse, displayWarning } from '@/responseMessage.js';

    const props = defineProps(['student', 'id','classes','sections','groups','sessions']);

    const form = useForm({ 
        name: props.student?.name ?? '',
        father_name: props.student?.father_name ?? '',
        mother_name: props.student?.mother_name ?? '',
        phone: props.student?.phone ?? '',
        email: props.student?.email ?? '',
        address: props.student?.address ?? '',
        date_of_birth: props.student?.date_of_birth ?? '',
        admission_date: props.student?.admission_date ?? '',
        photo: props.student?.photo ?? '',
        session_id: props.student?.session_id ?? '',
        class_id: props.student?.class_id ?? '',
        section_id: props.student?.section_id ?? '',
        group_id: props.student?.group_id ?? '',
        password: '' ,
        _method: props.student?.id ? 'put' : 'post',
    });
    const filteredGroups = ref([]);
    const filteredSections = ref([]);


    const fetchGroupsByClass = async (classId) => {
        if (!classId) {
            return displayWarning({ message: "Please select a class." });
        }

        try {
            const responses = await axios.get(route("backend.group.byClass", { classId }));
            filteredGroups.value = responses.data;
            const sectionResponse = await axios.get(route("backend.section.byClass", { classId }));
            filteredSections.value = sectionResponse.data.map(section => ({
            ...section,
            isFull: section.current_students >= section.total_sit,
        }));
    } catch (error) {
        displayWarning({ message: "Failed to load groups or sections." });
        }
    };
    const handlePhotoChange = (event) => {
        const file = event.target.files[0];
        form.photo = file;

        // Display photo preview
        const reader = new FileReader();
        reader.onload = (e) => {
            form.photoPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    };

    const submit = () => {
        const routeName = props.id ? route('backend.student.update', props.id) : route('backend.student.store');
        form.transform(data => ({
            ...data,
            remember: '',
            isDirty: false,
        })).post(routeName, {

            onSuccess: (response) => {
                if (!props.id)
                    form.reset();
                displayResponse(response);
            },
            onError: (errorObject) => {

                displayWarning(errorObject);
            },
        });
    };

</script>

    <template>
        <BackendLayout>
            <div
                class="w-full mt-3 transition duration-1000 ease-in-out transform bg-white border border-gray-700 rounded-md shadow-lg shadow-gray-800/50 dark:bg-slate-900">

                <div
                    class="flex items-center justify-between w-full text-gray-700 bg-gray-100 rounded-md shadow-md dark:bg-gray-800 dark:text-gray-200 shadow-gray-800/50">
                    <div>
                        <h1 class="p-4 text-xl font-bold dark:text-white">{{ $page.props.pageTitle }}</h1>
                    </div>
                    <div class="p-4 py-2">
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-4">
                    <AlertMessage />
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4">

                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="session_id" value="Session Year" />
                            <select id="session_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.session_id">
                                <option value="">Select Session</option>
                                <option v-for="session in sessions" :key="session.id" :value="session.id">{{ session.session_year }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.session_id" />
                        </div>
                        <!-- Class ID -->
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="class_id" value="Class"/>
                            <select id="class_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600" @change="fetchGroupsByClass(form.class_id)"
                                v-model="form.class_id">
                                <option value="">Select Class</option>
                                <option v-for="classe in classes" :key="classe.id" :value="classe.id">{{ classe.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.class_id" />
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="section_id" value="Section" />
                            <select id="section_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.section_id">
                                <option value="">Select Section</option>
                                <option v-for="section in filteredSections" :key="section.id" :value="section.id" 
                                    :disabled="section.isFull">
                                    {{ section.name }} ({{ section.current_students }}/{{ section.total_sit }})
                                </option>
                            </select>
                        </div>

                        <!-- Group ID -->
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="group_id" value="Group" />
                            <select id="class_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.group_id">
                                <option value="">Select Group</option>
                                <option v-for="group in filteredGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.group_id" />
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="photo" value="Photo" />
                            <div v-if="form.photoPreview">
                                <img :src="form.photoPreview" alt="Photo Preview" class="max-w-xs mt-2" height="60"
                                    width="60" />
                            </div>
                            <input id="photo" type="file" accept="image/*"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                @change="handlePhotoChange" />
                            <InputError class="mt-2" :message="form.errors.photo" />
                        </div>
                        <!-- Name -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="name" value="Name" />
                        <input 
                            id="name"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.name" 
                            type="text" 
                            placeholder="Name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Father Name -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="father_name" value="Father's Name" />
                        <input 
                            id="father_name"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.father_name" 
                            type="text" 
                            placeholder="Father's Name" />
                        <InputError class="mt-2" :message="form.errors.father_name" />
                        </div>

                        <!-- Mother Name -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="mother_name" value="Mother's Name" />
                        <input 
                            id="mother_name"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.mother_name" 
                            type="text" 
                            placeholder="Mother's Name" />
                        <InputError class="mt-2" :message="form.errors.mother_name" />
                        </div>

                        <!-- Phone -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="phone" value="Phone" />
                        <input 
                            id="phone"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.phone" 
                            type="text" 
                            placeholder="Phone" />
                        <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <!-- Email -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="email" value="Email" />
                        <input 
                            id="email"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.email" 
                            type="email" 
                            placeholder="Email" />
                        <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Address -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="address" value="Address" />
                        <input 
                            id="address"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.address" 
                            type="text" 
                            placeholder="Address" />
                        <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <!-- Date of Birth -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="date_of_birth" value="Date of Birth" />
                        <input 
                            id="date_of_birth"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.date_of_birth" 
                            type="date" 
                            placeholder="Date of Birth" />
                        <InputError class="mt-2" :message="form.errors.date_of_birth" />
                        </div>

                        <!-- Admission Date -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="admission_date" value="Admission Date" />
                        <input 
                            id="admission_date"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.admission_date" 
                            type="date" 
                            placeholder="Admission Date" />
                        <InputError class="mt-2" :message="form.errors.admission_date" />
                        </div>
                        <!-- Password -->
                        <div class="col-span-1 md:col-span-1">
                        <InputLabel for="password" value="Password" />
                        <input 
                            id="password"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.password" 
                            type="password" 
                            placeholder="Password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            {{ ((props.id ?? false) ? 'Update' : 'Create') }}
                        </PrimaryButton>
                    </div>
                </form>

            </div>
        </BackendLayout>
    </template>

