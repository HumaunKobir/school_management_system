

<script setup>
    import { ref, onMounted } from 'vue';
    import BackendLayout from '@/Layouts/BackendLayout.vue';
    import { router, useForm, usePage } from '@inertiajs/vue3';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AlertMessage from '@/Components/AlertMessage.vue';
    import { displayResponse, displayWarning } from '@/responseMessage.js';

    const props = defineProps(['teacher', 'id']);

    const form = useForm({
        name: props.teacher?.name ?? '',
        father_name: props.teacher?.father_name ?? '',
        mother_name: props.teacher?.mother_name ?? '',
        phone: props.teacher?.phone ?? '',
        email: props.teacher?.email ?? '',
        address: props.teacher?.address ?? '',
        date_of_birth: props.teacher?.date_of_birth ?? '',
        education_level: props.teacher?.education_level ?? '',
        jonning_date: props.teacher?.jonning_date ?? '',
        photo: props.teacher?.photo ?? '',
        password: props.teacher?.password ?? '',
        file: props.teacher?.file ?? '',

        imagePreview: props.teacher?.photo ?? "",
        filePreview: props.teacher?.file ?? "",
        _method: props.teacher?.id ? 'put' : 'post',
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
    const handlefileChange = (event) => {
        const file = event.target.files[0];
        form.file = file;
    };
    const submit = () => {
        const routeName = props.id ? route('backend.teacher.update', props.id) : route('backend.teacher.store');
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

                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="name" value="Name" />
                            <input id="name"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.name" type="text" placeholder="Name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="father_name" value="Father Name" />
                            <input id="father_name"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.father_name" type="text" placeholder="Father Name" />
                            <InputError class="mt-2" :message="form.errors.father_name" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="mother_name" value="Mother Name" />
                            <input id="mother_name"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.mother_name" type="text" placeholder="Mother Name" />
                            <InputError class="mt-2" :message="form.errors.mother_name" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="phone" value="Phone Number" />
                            <input id="phone"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.phone" type="text" placeholder="Phone Number" />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="email" value="Email" />
                            <input id="email"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.email" type="email" placeholder="Email" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="address" value="Address" />
                            <input id="address"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.address" type="text" placeholder="Address" />
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="date_of_birth" value="Date Of Birth" />
                            <input id="date_of_birth"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.date_of_birth" type="date" placeholder="Date Of Birth" />
                            <InputError class="mt-2" :message="form.errors.date_of_birth" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="education_level" value="Education Level" />
                            <input id="education_level"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.education_level" type="text" placeholder="Education Level" />
                            <InputError class="mt-2" :message="form.errors.education_level" />
                        </div>
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="jonning_date" value="Joning Date" />
                            <input id="jonning_date"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.jonning_date" type="date" placeholder="Joning Date" />
                            <InputError class="mt-2" :message="form.errors.jonning_date" />
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
                        <div class="col-span-1 md:col-span-2">
                            <InputLabel for="file" value="CV Upload" />
                            <input id="file"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                type="file"@change="handlefileChange"/>
                            <InputError class="mt-2" :message="form.errors.file" />
                        </div>
                       
                        <div class="col-span-1 md:col-span-1">
                            <InputLabel for="password" value="Password" />
                            <input id="password"
                                class="block w-full p-2 text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200 focus:border-indigo-300 dark:focus:border-slate-600"
                                v-model="form.password" type="password" placeholder="Password" />
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

