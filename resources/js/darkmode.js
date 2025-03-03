// Check localStorage for `theme` attribute, indicating light/dark mode preference
// If unset, set to default of `light`.

class ThemeController
{
    constructor()
    {
        this.currentTheme = null;
    }

    // update this.currentTheme based on localStorage
    pullThemeFromStorage()
    {
        if('theme' in localStorage)
        {
            if(localStorage.getItem('theme') === 'light')
            {
                this.currentTheme = 'light';
            }
            if(localStorage.getItem('theme') === 'dark')
            {
                this.currentTheme = 'dark';
            }
        }
        else
        {
            this.currentTheme = 'light';
            localStorage.setItem('theme', 'light');
        }
    }

    // push a new value to localStorage `theme` attribute
    pushThemeToStorage(newTheme)
    {
        if(newTheme === 'light')
        {
            localStorage.setItem('theme', 'light');
        }
        if(newTheme === 'dark')
        {
            localStorage.setItem('theme', 'dark');
        }
    }

    // modify the document based on the state of `theme` in localStorage
    applyTheme()
    {
        // make sure theme state is up to date
        this.pullThemeFromStorage();

        // modify document
        if(this.currentTheme === 'light')
            {
                document.documentElement.classList.remove('dark');
            }
            if(this.currentTheme === 'dark')
            {
                document.documentElement.classList.add('dark');
            }
    }

    // check what the current theme setting is, invert it, and apply
    toggleTheme()
    {
        this.pullThemeFromStorage();
        var newTheme = "";
        if(this.currentTheme === 'light')
        {
            newTheme = 'dark';
        }
        if(this.currentTheme === 'dark')
        {
            newTheme = 'light';
        }
        this.pushThemeToStorage(newTheme);

        this.applyTheme();
    }
}

// default behavior, create a ThemeController object and apply whatever is set in localStorage
window.themeController = new ThemeController();
window.themeController.applyTheme();