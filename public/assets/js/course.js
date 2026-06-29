// YouTube player variable
var player;

// Declare currentVideoUrl (assuming it's defined elsewhere, e.g., in the template)
var currentVideoUrl = window.currentVideoUrl || ""; // Default to empty string if not defined

// Load the YouTube IFrame Player API code asynchronously
var tag = document.createElement("script");
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName("script")[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// This function gets called when the YouTube API has loaded
function onYouTubeIframeAPIReady() {
  // Extract video ID from the URL
  var videoId = getYouTubeVideoId(currentVideoUrl);

  if (!videoId) {
    console.error("Invalid YouTube URL:", currentVideoUrl);
    return;
  }

  // Create the player
  player = new YT.Player("youtube-player", {
    height: "100%",
    width: "100%",
    videoId: videoId,
    playerVars: {
      playsinline: 1,
      controls: 0,
      rel: 0,
      showinfo: 0,
      modestbranding: 1,
      enablejsapi: 1,
    },
    events: {
      onReady: onPlayerReady,
      onStateChange: onPlayerStateChange,
    },
  });
}

// Extract YouTube video ID from URL
function getYouTubeVideoId(url) {
  var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  var match = url.match(regExp);
  return match && match[2].length === 11 ? match[2] : null;
}

// Called when the player is ready
function onPlayerReady(event) {
  console.log("YouTube player is ready");

  // Initialize custom controls
  initCustomControls();

  // Update progress bar max value
  var progressBar = document.getElementById("progress-bar");
  if (progressBar) {
    progressBar.max = player.getDuration();
  }

  // Update time display
  updateTimeDisplay();
}

// Called when the player's state changes
function onPlayerStateChange(event) {
  console.log("Player state changed:", event.data);

  var playPauseBtn = document.getElementById("play-pause-btn");

  if (event.data === YT.PlayerState.PLAYING) {
    if (playPauseBtn) {
      playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
    }

    // Start progress update interval
    startProgressInterval();
  } else if (
    event.data === YT.PlayerState.PAUSED ||
    event.data === YT.PlayerState.ENDED
  ) {
    if (playPauseBtn) {
      playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
    }

    // Stop progress update interval
    stopProgressInterval();
  }
}

// Progress update interval
var progressInterval;

function startProgressInterval() {
  // Clear any existing interval
  stopProgressInterval();

  // Update progress every 100ms
  progressInterval = setInterval(() => {
    updateProgress();
  }, 100);
}

function stopProgressInterval() {
  if (progressInterval) {
    clearInterval(progressInterval);
  }
}

function updateProgress() {
  if (!player || typeof player.getCurrentTime !== "function") return;

  var progressBar = document.getElementById("progress-bar");
  if (progressBar) {
    var currentTime = player.getCurrentTime();
    progressBar.value = currentTime;
  }

  updateTimeDisplay();
}

function updateTimeDisplay() {
  if (!player || typeof player.getCurrentTime !== "function") return;

  var timeDisplay = document.getElementById("time-display");
  if (!timeDisplay) return;

  var currentTime = player.getCurrentTime() || 0;
  var duration = player.getDuration() || 0;

  var currentMinutes = Math.floor(currentTime / 60);
  var currentSeconds = Math.floor(currentTime % 60);
  var durationMinutes = Math.floor(duration / 60);
  var durationSeconds = Math.floor(duration % 60);

  timeDisplay.textContent = `${currentMinutes}:${
    currentSeconds < 10 ? "0" : ""
  }${currentSeconds} / ${durationMinutes}:${
    durationSeconds < 10 ? "0" : ""
  }${durationSeconds}`;
}

// Initialize custom controls
function initCustomControls() {
  var videoControls = document.getElementById("video-controls");
  var playPauseBtn = document.getElementById("play-pause-btn");
  var progressBar = document.getElementById("progress-bar");
  var skipBackBtn = document.getElementById("skip-back-btn");
  var skipForwardBtn = document.getElementById("skip-forward-btn");
  var muteBtn = document.getElementById("mute-btn");
  var volumeSlider = document.getElementById("volume-slider");
  var fullscreenBtn = document.getElementById("fullscreen-btn");
  var youtubePlayerElement = document.getElementById("youtube-player");

  var controlsTimeout;

  // Show controls initially
  if (videoControls) {
    videoControls.style.opacity = "1";
  }

  // Play/Pause functionality
  if (playPauseBtn) {
    playPauseBtn.addEventListener("click", () => {
      if (!player) return;

      var state = player.getPlayerState();
      if (state === YT.PlayerState.PLAYING) {
        player.pauseVideo();
      } else {
        player.playVideo();
      }
    });
  }

  // Click on video to play/pause
  if (youtubePlayerElement) {
    youtubePlayerElement.addEventListener("click", (e) => {
      // Prevent clicks on the iframe from triggering this
      if (e.target !== youtubePlayerElement) return;

      if (!player) return;

      var state = player.getPlayerState();
      if (state === YT.PlayerState.PLAYING) {
        player.pauseVideo();
      } else {
        player.playVideo();
      }
    });
  }

  // Seek functionality
  if (progressBar) {
    progressBar.addEventListener("input", () => {
      if (!player) return;

      player.seekTo(Number.parseFloat(progressBar.value), true);
    });
  }

  // Skip forward/backward
  if (skipBackBtn) {
    skipBackBtn.addEventListener("click", () => {
      if (!player) return;

      var currentTime = player.getCurrentTime();
      player.seekTo(Math.max(currentTime - 10, 0), true);
    });
  }

  if (skipForwardBtn) {
    skipForwardBtn.addEventListener("click", () => {
      if (!player) return;

      var currentTime = player.getCurrentTime();
      var duration = player.getDuration();
      player.seekTo(Math.min(currentTime + 10, duration), true);
    });
  }

  // Volume control
  if (volumeSlider) {
    volumeSlider.addEventListener("input", () => {
      if (!player) return;

      var volume = Number.parseInt(volumeSlider.value);
      player.setVolume(volume);
      updateVolumeIcon(volume);
    });
  }

  if (muteBtn) {
    muteBtn.addEventListener("click", () => {
      if (!player) return;

      var isMuted = player.isMuted();
      if (isMuted) {
        player.unMute();
        var volume = player.getVolume();
        if (volumeSlider) {
          volumeSlider.value = volume;
        }
        updateVolumeIcon(volume);
      } else {
        player.mute();
        updateVolumeIcon(0);
      }
    });
  }

  function updateVolumeIcon(volume) {
    if (!muteBtn) return;

    if (volume === 0 || (player && player.isMuted())) {
      muteBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
    } else if (volume < 50) {
      muteBtn.innerHTML = '<i class="fas fa-volume-down"></i>';
    } else {
      muteBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
    }
  }

  // Fullscreen
  if (fullscreenBtn && youtubePlayerElement) {
    fullscreenBtn.addEventListener("click", () => {
      if (document.fullscreenElement) {
        document.exitFullscreen();
      } else {
        youtubePlayerElement.requestFullscreen();
      }
    });
  }

  // Show/hide controls
  var playerContainer = youtubePlayerElement.parentElement;
  if (playerContainer && videoControls) {
    playerContainer.addEventListener("mousemove", () => {
      videoControls.style.opacity = "1";
      clearTimeout(controlsTimeout);

      if (player && player.getPlayerState() === YT.PlayerState.PLAYING) {
        controlsTimeout = setTimeout(() => {
          videoControls.style.opacity = "0";
        }, 3000);
      }
    });

    playerContainer.addEventListener("mouseleave", () => {
      if (player && player.getPlayerState() === YT.PlayerState.PLAYING) {
        videoControls.style.opacity = "0";
      }
    });
  }
}

// Module collapsible functionality
function initModuleCollapsible() {
  var moduleTriggers = document.querySelectorAll(".module-trigger");

  moduleTriggers.forEach((trigger) => {
    trigger.addEventListener("click", function () {
      var moduleContainer = this.closest(".module-container");
      var moduleContent = moduleContainer.querySelector(".module-content");
      var moduleIcon = this.querySelector(".module-icon");

      // Toggle the content visibility
      if (moduleContent.classList.contains("hidden")) {
        moduleContent.classList.remove("hidden");
        moduleContent.classList.add("block");
        moduleIcon.classList.add("rotate-90");
      } else {
        moduleContent.classList.add("hidden");
        moduleContent.classList.remove("block");
        moduleIcon.classList.remove("rotate-90");
      }
    });
  });
}

// Mark as complete functionality
function initMarkComplete() {
  var markCompleteBtn = document.getElementById("mark-complete-btn");
  if (!markCompleteBtn) return;

  markCompleteBtn.addEventListener("click", function () {
    const lessonId = this.getAttribute("data-lesson-id");
    const pathParts = window.location.pathname.split("/");
    const courseId = this.getAttribute("data-course-id") || pathParts[2];

    if (!courseId || !lessonId) {
      console.error("Missing course or lesson id for mark complete", {
        courseId,
        lessonId,
      });
      return;
    }

    // Send AJAX request to mark lesson as complete
    fetch(`/api/mark-complete/${courseId}/${lessonId}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Update UI to show lesson as completed
          markCompleteBtn.innerHTML =
            '<i class="fas fa-check-circle"></i> Completed';
          markCompleteBtn.classList.add(
            "bg-green-100",
            "text-green-800",
            "border-green-200"
          );
          markCompleteBtn.classList.remove(
            "border-gray-300",
            "hover:bg-gray-50"
          );

          // Find and update the lesson item in the sidebar
          var lessonItem = document.querySelector(
            `.lesson-item[href$="/${lessonId}"]`
          );
          if (lessonItem) {
            var icon = lessonItem.querySelector("i");
            if (icon) {
              icon.classList.remove("far", "fa-circle", "text-gray-400");
              icon.classList.add("fas", "fa-check-circle", "text-blue-600");
            }
          }
        } else {
          console.error("Failed to mark lesson as complete:", data.message);
        }
      })
      .catch((error) => {
        console.error("Error marking lesson as complete:", error);
      });
  });
}

// Lesson navigation functionality
function initLessonNavigation() {
  const prevBtn = document.querySelector(".prev-lesson-btn");
  const nextBtn = document.querySelector(".next-lesson-btn");

  if (!prevBtn || !nextBtn) return;

  const courseId = window.location.pathname.split("/")[2];
  const lessonId = window.location.pathname.split("/")[4];

  // Fetch lesson navigation data
  fetch(`/api/lesson-navigation/${courseId}/${lessonId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update previous button
        if (data.previousLesson) {
          prevBtn.addEventListener("click", () => {
            window.location.href = `/course/${courseId}/lesson/${data.previousLesson.id}`;
          });
        } else {
          prevBtn.disabled = true;
          prevBtn.classList.add("opacity-50", "cursor-not-allowed");
        }

        // Update next button
        if (data.nextLesson) {
          nextBtn.addEventListener("click", () => {
            window.location.href = `/course/${courseId}/lesson/${data.nextLesson.id}`;
          });
        } else {
          nextBtn.disabled = true;
          nextBtn.classList.add("opacity-50", "cursor-not-allowed");
        }
      }
    })
    .catch((error) => {
      console.error("Error fetching lesson navigation:", error);
    });
}

// Initialize everything when the DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Module collapsible functionality
  initModuleCollapsible();

  // Mark as complete functionality
  initMarkComplete();

  // Lesson navigation functionality
  initLessonNavigation();

  // Note: YouTube player initialization happens via onYouTubeIframeAPIReady
  // which is called automatically by the YouTube API
});
