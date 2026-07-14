const SKIP_STEP_SECONDS = 10;

const elements = {
    trackArt: document.querySelector('.track-art'),
    trackName: document.querySelector('.track-name'),
    playPauseBtn: document.querySelector('.playPause-track'),
    randomTrack: document.querySelector('.random-track'),
    seekSlider: document.querySelector('.seek_slider'),
    volumeSlider: document.querySelector('.volume_slider'),
    currentTime: document.querySelector('.current-time'),
    remainsTime: document.querySelector('.remains-time'),
    randomIcon: document.querySelector('.fa-shuffle'),
    autoStop: document.querySelector('#autoShutdown'),
    storyRows: document.querySelectorAll('.story-row')
};

const currentTrack = document.createElement('audio');

let trackList = {};
let playingIndex = 0;
let isPlaying = false;
let playRandom = false;
let updateTimer = null;
let updateAutoStopTimer = null;
let autoStopTimestampEnd = 0;

// load all stories
let getStoriesUrl = '/api/stories.php';
if (isFavorite()) {
    getStoriesUrl += '?favorites';
}
fetch(getStoriesUrl)
    .then(response => {
        if (!response.ok) throw new Error('Response error');
        return response.json();
    })
    .then(data => {
        trackList = data;

        // load story from url
        if (selectedStory !== null) {
            loadTrack(selectedStory);
        }
    })
    .catch(err => console.error('Failed to load stories:', err));

function isFavorite() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.has('favorites');
}

// load user settings (if user is logged)
function loadUserSettings() {
    fetch('/api/get-settings.php')
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch settings');
            return response.json();
        })
        .then(settings => {
            if (settings.shuffle === 1) {
                randomTrackEnable();
            }

            if (settings.volume) {
                setVolume(settings.volume);
            }
        })
        .catch(err => console.error('Error loading settings:', err));
}

function customTrack(trackIndex) {
    addToHistory(trackIndex);
    loadTrack(trackIndex);
    playTrack();
    return false;
}

function loadTrack(trackIndex) {
    playingIndex = trackIndex;

    clearInterval(updateTimer);
    resetTrackProgress();

    currentTrack.src = `${BASE_AUDIO_URL}${trackIndex}.mp3`;
    currentTrack.load();

    checkTime();
    setTrackName(trackIndex);

    updateTimer = setInterval(setUpdate, 1000);
    currentTrack.addEventListener('ended', nextTrack, { once: true });
}

function getTrack(trackId) {
    return trackList.find(t => t.id === trackId);
}

function setTrackName(trackId) {
    const track = getTrack(trackId);
    const name = track ? track.name : '';
    if (elements.trackName) {
        elements.trackName.textContent = name;
        elements.trackName.dataset.text = name;
    }
    document.title = `${name} - Rozprávky pre deti na dobrú noc`;
}

function resetTrackProgress() {
    if (elements.currentTime) elements.currentTime.textContent = "00:00";
    if (elements.remainsTime) elements.remainsTime.textContent = "00:00";
    if (elements.seekSlider) elements.seekSlider.value = 0;
}

function randomTrack() {
    if (playRandom) {
        randomTrackDisable();
    } else {
        randomTrackEnable();
    }

    saveSettings();
}
function randomTrackEnable() {
    playRandom = true;
    elements.randomIcon.classList.add('randomActive');
}
function randomTrackDisable() {
    playRandom = false;
    elements.randomIcon.classList.remove('randomActive');
}

function repeatTrack() {
    loadTrack(playingIndex);
    playTrack();
}

function playPauseTrack() {
    if (!currentTrack.currentSrc) {
        alert('Najskôr si musíte vybrať rozprávku zo zoznamu nižšie.');
        return;
    }
    isPlaying ? pauseTrack() : playTrack();
}

function playTrack() {
    if (updateAutoStopTimer === null) {
        startUpdateAutoStopTimer();
    }

    currentTrack.play().catch(err => console.error("Playback failed:", err));
    isPlaying = true;
    elements.trackArt.classList.add('rotate');

    updatePlayPauseIcon('fa-pause-circle');
}

function pauseTrack() {
    currentTrack.pause();
    isPlaying = false;
    elements.trackArt.classList.remove('rotate');

    updatePlayPauseIcon('fa-play-circle');
}

function updatePlayPauseIcon(iconClass) {
    const icon = elements.playPauseBtn?.querySelector('i');
    if (icon) {
        icon.className = `fa ${iconClass} fa-5x`;
    }
}

function nextTrack() {
    const trackIds = trackList.map(track => Number(track.id));
    let nextId;

    if (!playRandom) {
        const index = trackIds.indexOf(playingIndex);
        nextId = trackIds[index + 1];
    } else {
        const filteredTracks = trackIds.filter(id => id !== playingIndex);
        if (filteredTracks.length > 0) {
            const randomIndex = Math.floor(Math.random() * filteredTracks.length);
            nextId = filteredTracks[randomIndex];
        } else {
            nextId = playingIndex;
        }
    }

    nextId = nextId !== undefined ? nextId : trackIds[0];

    addToHistory(nextId);
    loadTrack(nextId);
    playTrack();
}

function prevTrack() {
    const ids = trackList.map(track => Number(track.id));
    const index = ids.indexOf(playingIndex);
    const prevId = ids[index - 1] !== undefined ? ids[index - 1] : ids[0];

    if (ids[index - 1] !== undefined) {
        addToHistory(prevId);
    }

    loadTrack(prevId);
    playTrack();
}

function seekTo() {
    if (!isNaN(currentTrack.duration) && elements.seekSlider) {
        currentTrack.currentTime = currentTrack.duration * (elements.seekSlider.value / 100);
    }
}

function moveTimeline(seconds) {
    let newTime = currentTrack.currentTime + seconds;
    if (newTime < 0) newTime = 0;
    if (newTime >= currentTrack.duration) newTime = currentTrack.duration - 5;

    currentTrack.currentTime = newTime;
    setUpdate();
}

function seekBackward() { moveTimeline(-SKIP_STEP_SECONDS); }
function seekForward() { moveTimeline(SKIP_STEP_SECONDS); }

function updateVolume() {
    if (!elements.volumeSlider) return;
    currentTrack.volume = elements.volumeSlider.value / 100;
    saveSettings();
}

function setVolume(vol) {
    if (elements.volumeSlider) {
        elements.volumeSlider.value = vol * 100;
        currentTrack.volume = vol;
    }
}

function checkTime() {
    const urlParams = new URLSearchParams(window.location.search);
    const time = urlParams.get('t');

    if (!time || currentTrack.currentTime !== 0) return;

    let sec = 0;
    if (time.includes(':')) {
        const [minT, secT] = time.split(":");
        sec = parseInt(minT, 10) * 60 + parseInt(secT, 10);
    } else {
        sec = parseInt(time, 10);
    }

    if (sec > 0) {
        currentTrack.currentTime = sec;
        removeTimeParameter();
    }
}

function removeTimeParameter() {
    const url = new URL(window.location.href);
    url.searchParams.delete('t');
    window.history.replaceState(window.history.state, document.title, url.pathname + url.search);
}

function setUpdate() {
    if (isNaN(currentTrack.duration)) return;

    if (elements.seekSlider) {
        elements.seekSlider.value = currentTrack.currentTime * (100 / currentTrack.duration);
    }
    if (elements.currentTime) {
        elements.currentTime.textContent = parseTime(currentTrack.currentTime);
    }
    if (elements.remainsTime) {
        elements.remainsTime.textContent = parseTime(currentTrack.duration - currentTrack.currentTime);
    }
}

function resetAutoStop() {
    if (!elements.autoStop) return;
    const autoStopActiveSeconds = parseInt(elements.autoStop.dataset.duration, 10 || 0);
    autoStopTimestampEnd = Date.now() + (autoStopActiveSeconds * 1000);
}

function startUpdateAutoStopTimer() {
    resetAutoStop();
    updateAutoStopTimer = setInterval(setUpdateAutoStopTimer, 1000);
}

function setUpdateAutoStopTimer() {
    const now = Date.now();

    if (now >= autoStopTimestampEnd) {
        pauseTrack();
        clearInterval(updateAutoStopTimer);
        updateAutoStopTimer = null;
        return;
    }

    if (elements.autoStop) {
        const remainSeconds = Math.ceil((autoStopTimestampEnd - now) / 1000);
        elements.autoStop.textContent = parseTime(remainSeconds);
    }
}

function parseTime(seconds, withHours = false) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);

    const pad = (num) => String(num).padStart(2, '0');

    let output = '';
    if (h > 0 || withHours) {
        output += (h < 10 && h > 0 ? '0' + h : h) + ':';
    }

    return `${output}${pad(m)}:${pad(s)}`;
}

function searchInList(value) {
    if (value === '') {
        elements.storyRows.forEach(el => el.style.display = 'block');
        return;
    }

    const normalizedQuery = normalize(value.toLowerCase());

    elements.storyRows.forEach(row => {
        const storyName = row.querySelector('a').innerText;

        row.style.display = normalize(storyName.toLowerCase()).includes(normalizedQuery)
            ? 'block'
            : 'none';
    });
}

function normalize(str) {
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function saveSettings() {
    const payload = {
        shuffle: playRandom ? 1 : 0,
        volume: currentTrack.volume
    };

    fetch('/api/save-settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    }).catch(err => console.error('Failed to save settings:', err));
}

function addToHistory(storyId) {
    const url = new URL(window.location.href);
    url.searchParams.set('story', storyId);
    history.pushState(null, null, url.toString());
}

function copyActualTimeToClipboard(el) {
    const currentUrl = new URL(window.location.href);
    const timeStr = Math.floor(currentTrack.currentTime).toString();
    currentUrl.searchParams.set('t', timeStr);

    copyToClipboard(currentUrl.href);
}

async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
}
