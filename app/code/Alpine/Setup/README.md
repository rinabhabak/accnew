#Alpine_Setup

This extension provides a helper with methods for creating pages, blocks, categories and other resources. 
It is then used by  Alpine_CmsSetup to create static pages and blocks. It not clear why this approach was 
chosen, the extension provides only helper and nothing else and CmsSetup extension has an empty helper 
that does just extends this helper to inherit the functionality.