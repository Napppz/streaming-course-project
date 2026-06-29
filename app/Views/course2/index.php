<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $courseData['title'] ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/course.css">
</head>

<body class="bg-gray-50 text-gray-900">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/user/courses/enrolled" class="flex items-center text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span>Kembali ke Daftar Kursus</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <span class="font-medium text-gray-700"><?= $courseData['title'] ?></span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/user/dashboard" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-home"></i>
                        <span class="ml-1">Dashboard</span>
                    </a>
                    <a href="/user/profile" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-user-circle"></i>
                        <span class="ml-1">Profil</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex flex-col lg:flex-row min-h-screen">
        <!-- Sidebar - Course Path -->
        <div class="w-full lg:w-96 border-r border-gray-200 bg-white flex flex-col h-screen overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h1 class="text-xl font-bold"><?= $courseData['title'] ?></h1>
                <div class="mt-2 flex items-center gap-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $currentLesson['progress_percentage_course'] ?>%"></div>
                    </div>
                    <span class="text-sm text-gray-500"><?= $currentLesson['progress_percentage_course'] ?>%</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto" id="course-modules">
                <?php foreach ($courseData['modules'] as $module): ?>
                    <div class="module-container border-b border-gray-200" data-module-id="<?= $module['id'] ?>">
                        <button
                            class="module-trigger w-full flex justify-between items-center p-4 hover:bg-gray-50 focus:outline-none <?= $module['id'] == $currentLesson['moduleId'] ? 'bg-gray-100' : '' ?>">
                            <div class="flex items-center gap-2">
                                <!-- </?php if ($module['completed']): ?>
                        -->
                                <?php if ($moduleLib->isModuleCompleted($module)): ?>
                                    <i class="fas fa-check-circle text-blue-600"></i>
                                <?php else: ?>
                                    <i class="far fa-circle text-gray-400"></i>
                                <?php endif; ?>
                                <span class="font-medium"><?= $module['title'] ?></span>
                            </div>
                            <i class="fas fa-chevron-right transform transition-transform module-icon <?= $module['id'] == $currentLesson['moduleId'] ? 'rotate-90' : '' ?>"></i>
                        </button>
                        <div class="module-content pl-4 pr-2 py-2 space-y-1 <?= $module['id'] == $currentLesson['moduleId'] ? 'block' : 'hidden' ?>">
                            <?php foreach ($module['lessons'] as $lesson): ?>
                                <a
                                    href="/course/<?= $currentLesson['courseId'] ?>/lesson/<?= $lesson['id'] ?>"
                                    class="lesson-item flex items-center gap-2 w-full text-left pl-6 pr-2 py-2 hover:bg-gray-50 rounded <?= $lesson['id'] == $currentLesson['lessonId'] ? 'bg-gray-100' : '' ?>">
                                    <?php if ($lesson['status'] === 'completed'): ?>
                                        <i class="fas fa-check-circle text-blue-600 text-sm"></i>
                                    <?php else: ?>
                                        <i class="far fa-circle text-gray-400 text-sm"></i>
                                    <?php endif; ?>
                                    <span class="text-sm truncate"><?= $lesson['title'] ?></span>
                                    <span class="text-xs text-gray-500 ml-auto"><?= $lesson['video_duration'] ?> minutes</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Main Content - Video Player -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold"><?= $currentLesson['title'] ?></h2>
                    <?php
                    $currentModuleTitle = '';
                    foreach ($courseData['modules'] as $module) {
                        if ($module['id'] == $currentLesson['moduleId']) {
                            $currentModuleTitle = $module['title'];
                            break;
                        }
                    }
                    ?>
                    <p class="text-sm text-gray-500"><?= $currentModuleTitle ?></p>
                </div>
                <div class="flex gap-2">
                    <button class="prev-lesson-btn px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Previous</button>
                    <button class="next-lesson-btn px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Next Lesson</button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto w-full h-full">
                <div class="aspect-video w-full h-2/3 bg-black">
                    <!-- YouTube Player will be inserted here -->
                    <div id="youtube-player" class="w-full h-full"></div>

                    <!-- Custom Controls -->
                    <div id="video-controls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4 opacity-0 transition-opacity duration-300">
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <input
                                type="range"
                                id="progress-bar"
                                class="w-full h-1 bg-gray-400 rounded-full appearance-none cursor-pointer"
                                value="0"
                                min="0"
                                max="100"
                                step="0.1">
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <!-- Play/Pause Button -->
                                <button id="play-pause-btn" class="text-white hover:text-blue-400">
                                    <i class="fas fa-play"></i>
                                </button>

                                <!-- Skip Buttons -->
                                <button id="skip-back-btn" class="text-white hover:text-blue-400">
                                    <i class="fas fa-backward"></i>
                                </button>
                                <button id="skip-forward-btn" class="text-white hover:text-blue-400">
                                    <i class="fas fa-forward"></i>
                                </button>

                                <!-- Time Display -->
                                <div id="time-display" class="text-white text-sm ml-2">
                                    0:00 / 0:00
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Volume Control -->
                                <div class="flex items-center gap-2">
                                    <button id="mute-btn" class="text-white hover:text-blue-400">
                                        <i class="fas fa-volume-up"></i>
                                    </button>
                                    <input
                                        type="range"
                                        id="volume-slider"
                                        class="w-20 h-1 bg-gray-400 rounded-full appearance-none cursor-pointer"
                                        min="0"
                                        max="100"
                                        step="1"
                                        value="100">
                                </div>

                                <!-- Fullscreen Button -->
                                <button id="fullscreen-btn" class="text-white hover:text-blue-400 ml-2">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Lesson Content</h3>
                        <button id="mark-complete-btn" class="flex items-center gap-2 px-3 py-1 text-sm border <?= $currentLesson['status'] === 'completed' ? 'bg-green-100 text-green-800 border-green-200' : 'border-gray-300 hover:bg-gray-50' ?> rounded" data-course-id="<?= $currentLesson['courseId'] ?>" data-lesson-id="<?= $currentLesson['lessonId'] ?>" data-module-id="<?= $currentLesson['moduleId'] ?>">
                            <i class="<?= $currentLesson['status'] === 'completed' ? 'fas' : 'far' ?> fa-check-circle"></i>
                            <?= $currentLesson['status'] === 'completed' ? 'Completed' : 'Mark as Complete' ?>
                        </button>
                    </div>

                    <hr class="my-4 border-gray-200">

                    <div class="prose prose-sm max-w-none">
                        <?= $currentLesson['content'] ?>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Resources</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium">Exercise Files</h4>
                                <p class="text-sm text-gray-500 mt-1">Download the exercise files for this lesson</p>
                                <button class="mt-2 px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Download</button>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium">Additional Reading</h4>
                                <p class="text-sm text-gray-500 mt-1">Explore more about the CSS Box Model</p>
                                <button class="mt-2 px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">View Resources</button>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- YouTube API will be loaded by the script -->
    <script>
        // Store the video URL from PHP to JavaScript
        var currentVideoUrl = "<?= $currentLesson['videoUrl'] ?>";
    </script>
    <script src="/assets/js/course.js"></script>
</body>

</html>
