

<script setup>
    import { ref, onMounted } from 'vue';
    import BackendLayout from '@/Layouts/BackendLayout.vue';
    import { router, useForm, usePage } from '@inertiajs/vue3';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AlertMessage from '@/Components/AlertMessage.vue';
    import { displayResponse, displayWarning } from '@/responseMessage.js';

    const props = defineProps(['classroutine', 'id','classes','sections','groups','subjects','rooms']);

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
        const routeName = props.id ? route('backend.classroutine.update', props.id) : route('backend.classroutine.store');
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
                        <select id="industry_news_category"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.class_id" type="text" placeholder="">
                            <option value="">Class</option>
                            <option v-for="classe in classes"
                                :key="classe.id" :value="classe.id">
                                {{ classe.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.class_id" />
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <InputLabel for="section_id" value="Section" />
                        <select id="section_id"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.section_id" type="text" placeholder="">
                            <option value="">Section</option>
                            <option v-for="section in sections"
                                :key="section.id" :value="section.id">
                                {{ section.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.section_id" />
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <InputLabel for="group_id" value="Group" />
                        <select id="group_id"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.group_id" type="text" placeholder="">
                            <option value="">section</option>
                            <option v-for="group in groups"
                                :key="group.id" :value="group.id">
                                {{ group.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.group_id" />
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <InputLabel for="subject_id" value="Subject" />
                        <select id="subject_id"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.subject_id" type="text" placeholder="">
                            <option value="">Subject</option>
                            <option v-for="subject in subjects"
                                :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
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
                            <option value="Saterday">Saterday</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Tharsday">Tharsday</option>
                            
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

