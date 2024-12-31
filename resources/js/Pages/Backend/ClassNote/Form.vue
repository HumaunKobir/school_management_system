

<script setup>
    import { ref, onMounted } from 'vue';
    import BackendLayout from '@/Layouts/BackendLayout.vue';
    import { router, useForm, usePage } from '@inertiajs/vue3';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AlertMessage from '@/Components/AlertMessage.vue';
    import { displayResponse, displayWarning } from '@/responseMessage.js';

    import { QuillEditor } from '@vueup/vue-quill';

    import '@vueup/vue-quill/dist/vue-quill.snow.css';
    
    const props = defineProps(['classnote', 'id','sessions','classes', 'sections', 'groups', 'subjects','teachers']);

    const form = useForm({
        session_id: props.classnote?.class_id ?? '',
        class_id: props.classnote?.class_id ?? '',
        section_id: props.classnote?.section_id ?? '',
        group_id: props.classnote?.group_id ?? '',
        teacher_id: props.classnote?.teacher_id ?? '',
        subject_id: props.classnote?.subject_id ?? '',
        date: props.classnote?.date ?? '',
        class_note: props.classnote?.class_note ?? '',
        note_photo: props.classnote?.note_photo ?? '',
        note_pdf: props.classnote?.note_pdf ?? '',

        imagePreview: props.classnote?.note_photo ?? "",
        filePreview: props.classnote?.note_pdf ?? "",
        _method: props.classnote?.id ? 'put' : 'post',
    });
    const filteredGroups = ref([]);
    const filteredSections = ref([]);
    const filteredSubjects = ref([]);


    const fetchGroupsByClass = async (classId) => {
        if (!classId) {
            return displayWarning({ message: "Please select a class." });
        }

        try {
            const responses = await axios.get(route("backend.groups.byClass", { classId }));
            filteredGroups.value = responses.data;
            const response = await axios.get(route("backend.sections.byClass", { classId}));
            filteredSections.value = response.data;
        } catch (error) {
            displayWarning({ message: "Failed to load groups." });
        }
    };

    const fetchSubjectsByGroup = async (groupId) => {
        if (!groupId) {
            return displayWarning({ message: "Please select a group." });
        }

        try {
            const response = await axios.get(route("backend.subjects.byGroup", { groupId }));
            filteredSubjects.value = response.data;
        } catch (error) {
            displayWarning({ message: "Failed to load subjects." });
        }
    };
    const handlePhotoChange = (event) => {
        const file = event.target.files[0];
        form.note_photo = file;

        // Display photo preview
        const reader = new FileReader();
        reader.onload = (e) => {
            form.photoPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    };
    const handlefileChange = (event) => {
        const file = event.target.files[0];
        form.note_pdf = file;
    };

    const submit = () => {
        const routeName = props.id ? route('backend.classnote.update', props.id) : route('backend.classnote.store');
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
                            <InputLabel for="class_id" value="Class" />
                            <select id="class_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.class_id" @change="fetchGroupsByClass(form.class_id)">
                                <option value="">Class</option>
                                <option v-for="classe in classes" :key="classe.id" :value="classe.id">{{ classe.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.class_id" />
                        </div>
                        <!-- Session Selection -->
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="session_id" value="Session Year" />
                            <select id="session_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.session_id">
                                <option value="">Select Session</option>
                                <option v-for="session in sessions" :key="session.id" :value="session.id">{{ session.session_year }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.session_id" />
                        </div>
                        <!-- Section Selection -->
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="section_id" value="Section" />
                            <select id="section_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.section_id">
                                <option value="">Select Section</option>
                                <option v-for="section in filteredSections" :key="section.id" :value="section.id">{{ section.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.section_id" />
                        </div>

                        <!-- Group Selection -->
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="group_id" value="Group" />
                            <select id="group_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.group_id" @change="fetchSubjectsByGroup(form.group_id)">
                                <option value="">Select Group</option>
                                <option v-for="group in filteredGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.group_id" />
                        </div>
                        <!-- Teacher Selection -->
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="teacher_id" value="Teacher" />
                            <select id="teacher_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.teacher_id">
                                <option value="">Select Teacher</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.teacher_id" />
                        </div>
                        <!-- Subject Selection -->
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="subject_id" value="Subject" />
                            <select id="subject_id" class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.subject_id">
                                <option value="">Select Subject</option>
                                <option v-for="subject in filteredSubjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.subject_id" />
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="date" value="Note Date"/>
                            <input id="date"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.date" type="date"/>
                            <InputError class="mt-2" :message="form.errors.date" />
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="note_photo" value="Note Photo" />
                            <div v-if="form.photoPreview">
                                <img :src="form.photoPreview" alt="Photo Preview" class="max-w-xs mt-2" height="60"
                                    width="60" />
                            </div>
                            <input id="note_photo" type="file" accept="image/*"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                @change="handlePhotoChange" />
                            <InputError class="mt-2" :message="form.errors.note_photo" />
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="note_pdf" value="Note Upload" />
                            <input id="note_pdf"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                type="file"@change="handlefileChange"/>
                            <InputError class="mt-2" :message="form.errors.note_pdf" />
                        </div>
                    </div>
                    <div class="col-span-2 md:col-span-4">
                        <InputLabel for="class_note" value="Class Note" />
                        <QuillEditor toolbar="full" v-model:content="form.class_note" contentType="html" theme="snow" />
                        <InputError class="mt-5" :message="form.errors.class_note" />
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

