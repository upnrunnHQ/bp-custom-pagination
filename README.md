# bp-custom-pagination

BuddyPress is using the [paginate_links()](https://codex.wordpress.org/Function_Reference/paginate_links) function for building paginated navigation links on members and groups directory pages. The [paginate_links()](https://codex.wordpress.org/Function_Reference/paginate_links) function is working fine on directory pages but we want to customize the output a little bit.

‌

We'd like to add the following changes to the default output of the [paginate_links()](https://codex.wordpress.org/Function_Reference/paginate_links) function.

‌

1. We will display number of total items.  
2. Instead of disabling the current page link, we will add text input so user can put any number to navigate pages easily.  
3. We have links for previous and next page but will also add two additional links to navigate to the first and last page easily and remove unnecessary links.
