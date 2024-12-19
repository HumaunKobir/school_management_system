

<script setup>
    import { ref, onMounted } from 'vue';
    import BackendLayout from '@/Layouts/BackendLayout.vue';
    import { router, useForm, usePage } from '@inertiajs/vue3';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AlertMessage from '@/Components/AlertMessage.vue';
    import { displayResponse, displayWarning } from '@/responseMessage.js';

    const props = defineProps(['subject', 'id','classes','groups']);

    const form = useForm({
        class_id: props.subject?.class_id ?? '',
        group_id: props.subject?.group_id ?? '',
        name: props.subject?.name ?? '',
        subject_code: props.subject?.subject_code ?? '',
        _method: props.subject?.id ? 'put' : 'post',
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
        const routeName = props.id ? route('backend.subject.update', props.id) : route('backend.subject.store');
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
                            <option value="">Select Class</option>
                            <option v-for="classe in classes"
                                :key="classe.id" :value="classe.id">
                                {{ classe.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.class_id" />
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <InputLabel for="group_id" value="Group" />
                        <select id="group_id"
                            class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                            v-model="form.group_id" type="text" placeholder="">
                            <option value="">Select Group</option>
                            <option v-for="group in groups"
                                :key="group.id" :value="group.id">
                                {{ group.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.group_id" />
                    </div>

                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="name" value="Subject Name" />
                            <input id="name"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.name" type="text" placeholder="Subject Name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="subject_code" value="Subject Code" />
                            <input id="subject_code"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.subject_code" type="text" placeholder="Subject Code" />
                            <InputError class="mt-2" :message="form.errors.subject_code" />
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

