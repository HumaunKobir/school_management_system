

<script setup>
    import { ref, onMounted,computed } from 'vue';
    import BackendLayout from '@/Layouts/BackendLayout.vue';
    import { router, useForm, usePage } from '@inertiajs/vue3';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AlertMessage from '@/Components/AlertMessage.vue';
    import { displayResponse, displayWarning } from '@/responseMessage.js';

    const props = defineProps(['attendance', 'id','classRoutines','teachers','sessions','subjects']);
    const teachers = ref([]);
    const classes = ref([]);
    const sections = ref([]);
    const subjects = ref([]);
    const students = ref([]);

    const fetchTeachers = async (sessionId) => {
        try {
            const response = await axios.get(`/teachers/${sessionId}`);
            teachers.value = response.data;
            classes.value = [];
            sections.value = [];
            subjects.value = [];
        } catch (error) {
            console.error(error);
        }
    };

    const fetchClasses = async (sessionId, teacherId) => {
        try {
            const response = await axios.get(`/classes/${sessionId}/${teacherId}`);
            classes.value = response.data;
            sections.value = [];
        } catch (error) {
            console.error(error);
        }
    };

    const fetchSections = async (classId) => {
        try {
            const response = await axios.get(`/sections/${classId}`);
            sections.value = response.data;
        } catch (error) {
            console.error(error);
        }
    };
    const fetchSubjects = async (sectionId) => {
        try {
            const response = await axios.get(`/subjects/${sectionId}`);
            subjects.value = response.data;
        } catch (error) {
            console.error(error);
        }
    };
    const fetchStudents = async (sessionId, classId, sectionId) => {
        try {
            const response = await axios.get(`/students/${sessionId}/${classId}/${sectionId}`);
            students.value = response.data;
        } catch (error) {
            console.error(error);
        }
    };
    const totalStudents = computed(() => students.value.length);

    const presentStudents = computed(() =>
    students.value.filter(student => student.student_status === "1").length
    );

    const absentStudents = computed(() =>
        students.value.filter(student => student.student_status === "0").length
    );

    const updateStudentStatus = (index, student_status) => {
        // Update the student status in the students array
        students.value[index].student_status = student_status;
    };
    const form = useForm({
        name: props.event?.name ?? '',
        _method: props.event?.id ? 'put' : 'post',
    });

    const submit = () => {
        const routeName = props.id ? route('backend.attendance.update', props.id) : route('backend.attendance.store');
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
                        <h1 class="p-4 text-xl font-bold dark:text-white"></h1>
                    </div>
                    <div class="p-4 py-2">
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-4">
                    <AlertMessage />
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4">
                        <div class="col-span-2 md:col-span-1">
                            <InputLabel for="session_id" value="Session Year" />
                            <select class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600" id="session_id" v-model="form.session_id" @change="fetchTeachers(form.session_id)">
                                <option v-for="session in sessions" :key="session.id" :value="session.id">{{ session.session_year }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.session_id"/>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <InputLabel for="teacher_id" value="Teacher Name" />
                            <select class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600" id="teacher_id" v-model="form.teacher_id" @change="fetchClasses(form.session_id, form.teacher_id)">
                            <option value="">Select Teacher</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.teacher_id"/>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <InputLabel for="class_id" value="Class Name" />
                            <select class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600" id="class_id" v-model="form.class_id" @change="fetchSections(form.class_id)">
                            <option value="">Select Class</option>
                            <option v-for="classe in classes" :key="classe.id" :value="classe.id">{{ classe.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.class_id"/>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <InputLabel for="section_id" value="Section Name" />
                            <select class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600" id="section_id" v-model="form.section_id" @change="() => { fetchSubjects(form.section_id); fetchStudents(form.session_id, form.class_id, form.section_id); }">
                            <option value="">Select Section</option>
                            <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.section_id"/>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <InputLabel for="subject_id" value="Subject Name" />
                            <select class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600" id="subject_id" v-model="form.subject_id">
                            <option value="">Select Subject</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.subject_id"/>
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="date" value="Date" />
                            <input id="date"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.date" type="date" placeholder="" />
                            <InputError class="mt-2" :message="form.errors.date" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="time" value="Time" />
                            <input id="time"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.time" type="time" placeholder="" />
                            <InputError class="mt-2" :message="form.errors.time" />
                        </div>
                    </div>
                    <div class="col-span-4 md:col-span-4">
                        <h2 class="text-lg font-bold text-center py-4">Students</h2>
                        <div class="flex justify-center">
                            <table class="border-collapse border border-gray-300 w-full max-w-4xl text-center">
                                <thead class="bg-gray-100 border-b border-gray-300">
                                    <tr>
                                        <th class="p-2 border border-gray-300">SL</th>
                                        <th class="p-2 border border-gray-300">Student Id</th>
                                        <th class="p-2 border border-gray-300">Student Name</th>
                                        <th class="p-2 border border-gray-300">Class</th>
                                        <th class="p-2 border border-gray-300">Section</th>
                                        <th class="p-2 border border-gray-300">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-collapse border border-gray-300 w-full max-w-4xl text-center" v-for="(student, index) in students" :key="student.id">
                                        <td class="border text-sm ">{{ index + 1 }}</td>
                                        <td class="border text-sm ">{{ student.student_id }}</td>
                                        <td class="border text-sm ">{{ student.name }}</td>
                                        <td class="border text-sm ">{{ student.class.name }}</td>
                                        <td class="border text-sm ">{{ student.section.name }}</td>
                                        <td class="border text-sm ">
                                            <select
                                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                                v-model="students[index].student_status"
                                                @change="updateStudentStatus(index, students[index].student_status)">
                                                <option value="0">Absent</option>
                                                <option value="1">Present</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 border-t border-gray-300">
                                        <td colspan="3" class="p-2 font-bold">Totals</td>
                                        <td colspan="1" class="p-2">Total Students: {{ totalStudents }}</td>
                                        <td colspan="1" class="p-2">Present: {{ presentStudents }}</td>
                                        <td colspan="1" class="p-2">Absent: {{ absentStudents }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            {{ ((props.id ?? false) ? 'Update' : 'Submit') }}
                        </PrimaryButton>
                    </div>
                </form>

            </div>
        </BackendLayout>
    </template>

