<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stories</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
</head>

<body>
<main>
    <div class="container">
        <h1>Stories</h1>
        <div id="v-app">
            <div class="pagination">
                <button class="btn btn-default" @click="fetchStories(pagination.prev_page_url)" :disabled="!pagination.prev_page_url">
                Previous
                </button>
                <span>Page @{{pagination.current_page}} of @{{pagination.last_page}}</span>
                <button class="btn btn-default" @click="fetchStories(pagination.next_page_url)" :disabled="!pagination.next_page_url">Next
                </button>
            </div>
            <table class="table table-striped">
                <tr>
                    <th>#</th>
                    <th>Plot</th>
                    <th>Writer</th>
                    <th>Upvotes</th>
                    <th>Actions</th>
                </tr>
                <tr v-for="story in stories" is="story" :story="story"></tr>
            </table>
            <p class="lead">Here's a list of all your stories.
                <button @click="createStory()" class="btn btn-primary">Add a new one?</button>
            </p>
            <pre>@{{ $data }}</pre>
        </div>
    </div>
</main>

<template id="template-story-raw">
    <tr>
        <td>
            @{{story.id}}
        </td>
        <td class="col-md-6">
            <input v-if="story.editing" v-model="story.plot" class="form-control">
            </input>
            <!--in other occasions show the story plot-->
            <span v-else>
                                @{{story.plot}}
                            </span>
        </td>
        <td>
            <input v-if="story.editing" v-model="story.writer" class="form-control">
            </input>
            <!--in other occasions show the story writer-->
            <span v-else>
                                @{{story.writer}}
                            </span>
        </td>
        <td>
            @{{story.upvotes}}
        </td>
        <td>
            <div class="btn-group" v-if="!story.editing">
                <button @click="upvoteStory(story)" class="btn btn-primary">Upvote</button>
                <button @click="editStory(story)" class="btn btn-default">Edit</button>
                <button @click="deleteStory(story)" class="btn btn-danger">Delete</button>
            </div>
            <div class="btn-group" v-else>
                <!--If the story is taken from the db then it will have an id-->
                <button v-if="story.id" class="btn btn-primary" @click="updateStory(story)">Update Story
                </button>
                <!--If the story is new we want to store it-->
                <button v-else class="btn btn-success" @click="storeStory(story)">Save New Story</button>
                <!--Always show cancel-->
                <button @click="story.editing=false" class="btn btn-default">Cancel</button>
            </div>
        </td>
    </tr>
</template>

<script src="http://cdnjs.cloudflare.com/ajax/libs/vue/2.0.1/vue.js"></script>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/0.7.0/vue-resource.js"></script>
<script>
    Vue.component('story', {
        template: '#template-story-raw',
        props: ['story'],
        methods: {
            deleteStory: function (story) {
                var index = this.$parent.stories.indexOf(story)
                this.$parent.stories.splice(story, 1)
                this.$http.delete('/api/stories/' + story.id)
            },
            upvoteStory: function (story) {
                story.upvotes++;
                this.$http.patch('/api/stories/' + story.id, story)
            },
            editStory: function (story) {
                story.editing = true;
            },
            updateStory: function (story) {
                this.$http.patch('/api/stories/' + story.id, story)
                //Set editing to false to show actions again and hide the inputs
                story.editing = false;
            },
            storeStory: function (story) {
                this.$http.post('/api/stories/', story).then(function (response) {
                    /*
                     After the the new story is stored in the database fetch again all stories with
                     vm.fetchStories();
                     Or Better, update the id of the created story
                     */
                    Vue.set(story, 'id', response.data.id);

                    //Set editing to false to show actions again and hide the inputs
                    story.editing = false;
                });
            },
        }
    })

    new Vue({
        el: '#v-app',
        data: {
            stories: [],
            pagination: {},
            story: {}
        },
        mounted: function () {
            this.fetchStories()
        },
        methods: {
            createStory: function () {
                var newStory = {
                    plot: "",
                    upvotes: 0,
                    editing: true
                };
                this.stories.push(newStory);
            },
            fetchStories: function (page_url) {
                var vm = this;
                page_url = page_url || '/api/stories'
                this.$http.get(page_url)
                    .then(function (response) {
                        var storiesReady = response.data.data.map(function (story) {
                            story.editing = false;
                            return story
                        })
                        vm.makePagination(response.data)
                        this.stories = storiesReady
                    });
            },
            makePagination(data){
                //here we use response.data
                var pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    next_page_url: data.next_page_url,
                    prev_page_url: data.prev_page_url
                }
                this.pagination = pagination
            }
        }
    });
</script>
</body>
</html>