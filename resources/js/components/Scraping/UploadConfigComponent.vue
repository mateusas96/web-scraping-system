<template>
    <div class="container">
        <div class="row justify-content-center">
            <v-expansion-panels 
                focusable
                multiple
                v-model="panelOpened"
            >
                <v-expansion-panel>
                    <v-expansion-panel-header>
                        Upload files
                    </v-expansion-panel-header>
                    <v-expansion-panel-content class="mt-12">
                        <v-row no-gutters justify="center" align="center">
                            <v-col cols="10">
                                <v-file-input
                                    type="file"
                                    v-model="tempFiles"
                                    color="deep-purple accent-4"
                                    label="File input"
                                    multiple
                                    placeholder="Select your files"
                                    prepend-icon="mdi-paperclip"
                                    outlined
                                    accept=".txt"
                                    v-on:change="handleFilesChange"
                                >
                                    <template v-slot:selection="{ text }">
                                        <v-chip
                                            color="deep-purple accent-4"
                                            dark
                                            label
                                            small
                                        >
                                            {{ text }}
                                        </v-chip>
                                    </template>
                                </v-file-input>
                            </v-col>
                            <v-col cols="2" class="pl-8 pb-8">
                                <v-btn
                                    color="success"
                                    v-on:click="uploadFiles"
                                    :disabled="readyForUploadFiles < 1"
                                >
                                    Upload files
                                </v-btn>
                            </v-col>
                        </v-row>
                        <v-card v-if="readyForUploadFiles.length > 0" class="mx-auto">
                            <v-simple-table
                                fixed-header
                                height="250px"
                            >
                                <template v-slot:default>
                                <thead>
                                    <tr>
                                    <th class="text-left">
                                        Name
                                    </th>
                                    <th class="text-left">
                                        Action
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                    v-for="(file, index) in readyForUploadFiles"
                                    :key="index"
                                    >
                                    <td>{{ file.name }}</td>
                                    <td>
                                        <v-icon
                                            class="ml-4"
                                            v-on:click="handleFileDelete(index)"
                                            color="error darken-2"
                                        >
                                            mdi-trash-can
                                        </v-icon>
                                    </td>
                                    </tr>
                                </tbody>
                                </template>
                            </v-simple-table>
                        </v-card>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                color="error darken-2"
                                v-on:click="handleAllFilesDelete"
                                v-if="readyForUploadFiles.length > 0"
                            >
                                Delete all files
                            </v-btn>
                        </v-card-actions>
                    </v-expansion-panel-content>
                </v-expansion-panel>
                <v-expansion-panel v-on:click="banger">
                    <v-expansion-panel-header>
                        Manage files
                    </v-expansion-panel-header>
                    <v-expansion-panel-content>
                        
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-expansion-panels>
        </div>
    </div>
</template>

<script>
    export default {
        data: () => {
            return {
                panelOpened: [0],
                tempFiles: [],
                readyForUploadFiles: [],
            }
        },
        mounted() {

        },
        methods: {
            banger() {
                console.log("BANG BANG");
            },
            uploadFiles() {
                let formData = new FormData();

                formData.append('filesCount', this.readyForUploadFiles.length);

                this.readyForUploadFiles.forEach((value, index) => {
                    formData.append('file' + index, this.readyForUploadFiles[index]);
                })

                axios.post('api/file', formData)
                .then(({data}) => {
                    if (data.error) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: data.message + ': ',
                            text: data.not_uploaded_files,
                            timer: 7000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        })
                    } else {
                        this.readyForUploadFiles = [];
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            handleFilesChange() {
                if (this.readyForUploadFiles.length > 0) {
                    this.readyForUploadFiles.forEach((value, index) => {
                        this.tempFiles.forEach((val, idx) => {
                            if (value.name === val.name) {
                                this.$toastr.w('File: ' + value.name + ' already exists');
                                this.tempFiles.splice(idx, 1);
                            }
                        })
                    });
                }
                this.tempFiles.forEach((value, index) => {
                    if (value.type !== 'text/plain') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Only .txt file formats are supported',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                        })
                    } else {
                        this.readyForUploadFiles.push(this.tempFiles[index]);
                    }
                });
                this.tempFiles = [];
            },
            handleFileDelete(index) {
                this.readyForUploadFiles.splice(index, 1);
            },
            handleAllFilesDelete() {
                Swal.fire({
                    title: 'Are you sure you want to remove all files?',
                    showDenyButton: true,
                    confirmButtonText: 'Yes',
                    denyButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.readyForUploadFiles = [];
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'All files successfully removed',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                        })
                    }
                })
            }
        }
    }
</script>
