

<script setup>
import { ref, onMounted } from 'vue';
import BackendLayout from '@/Layouts/BackendLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AlertMessage from '@/Components/AlertMessage.vue';
import { displayResponse, displayWarning } from '@/responseMessage.js';

const props = defineProps(['classroutine', 'id', 'classes', 'sections', 'rooms', 'groups', 'subjects']);

const form = useForm({
    class_id: props.classroutine?.class_id ?? '',
    section_id: props.classroutine?.section_id ?? '',
    group_id: props.classroutine?.group_id ?? '',
    subject_id: props.classroutine?.subject_id ?? '',
    room_id: props.classroutine?.room_id ?? '',
    day: props.classroutine?.day ?? '',
    start_time: props.classroutine?.start_time ?? '',
    end_time: props.classroutine?.end_time ?? '',
    _method: props.classroutine?.id ? 'put' : 'post',
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
        const response = await axios.get(route("backend.sections.byClass", { classId }));
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

const submit = () => {
    const routeName = props.id ? route('backend.classroutine.update', props.id) : route('backend.classroutine.store');
    form.transform((data) => ({
        ...data,
        remember: '',
        isDirty: false,
    })).post(routeName, {
        onSuccess: (response) => {
            if (!props.id) form.reset();
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
                        <InputLabel for="room_id" value="Room" />
                        <select id="room_id"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.room_id" type="text" placeholder="">
                            <option value="">Class Room</option>
                            <option v-for="room in rooms"
                                :key="room.id" :value="room.id">
                                {{ room.room_number }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.room_id" />
                    </div>
                    

                    <div class="col-span-1 md:col-span-2">
                        <InputLabel for="day" value="Day" />
                        <select id="day"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.day" type="text" placeholder="">
                            <option value="">Select Day</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            
                        </select>
                        <InputError class="mt-2" :message="form.errors.day" />
                    </div>
                    <div class="col-span-1 md:col-span-1">
                            <InputLabel for="start_time" value="Start Time" />
                            <input id="start_time"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.start_time" type="time" placeholder="" />
                            <InputError class="mt-2" :message="form.errors.start_time" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="end_time" value="End Time" />
                            <input id="end_time"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.end_time" type="time" placeholder="" />
                            <InputError class="mt-2" :message="form.errors.end_time" />
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

