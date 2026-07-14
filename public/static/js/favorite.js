function switchFavorite(el, storyId) {
    const isSelected = el.dataset.selected === '1';
    const icon = el.querySelector('i');

    toggleHeartIcon(el, icon, !isSelected);

    fetch('/api/save-favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ story_id: parseInt(storyId, 10) })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server responded with an error');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                toggleHeartIcon(el, icon, isSelected);
                console.error('Failed to save favorite state on backend');
            } else {
                toggleHeartIcon(el, icon, data.is_favorite);
            }
        })
        .catch(err => {
            toggleHeartIcon(el, icon, isSelected);
            console.error('Failed to save favorite:', err);
        });
}

function toggleHeartIcon(el, icon, setActive) {
    if (setActive) {
        icon.className = 'fa-solid fa-heart';
        el.dataset.selected = '1';
    } else {
        icon.className = 'fa-regular fa-heart';
        el.dataset.selected = '0';
    }
}
