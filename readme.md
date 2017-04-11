# Factchecker's Schema.org ClaimReview manager for WordPress

## NOTE: developer edition
This is a new plugin and we are testing it in the wild before releasing it as a plugin in the WordPress plugin repository. 

Please install on your site, or in staging, and feel free to contribute with Pull Requests and become a contributor. 


## About this plugin
This is a WordPress plugin that allows publishers to add the new  [ClaimReview - schema.org](http://schema.org/ClaimReview) schema for publishing factchecking reports to their posts. 

You can read more about the labeling of fact checking articles in [Labeling fact-check articles in Google News](https://blog.google/topics/journalism-news/labeling-fact-check-articles-google-news/).



## Installing on a website

> This is currently a work in development - and while it should be fine to use with your site - it has not yet been released as a WordPress plugin. 


### Plugin and Settings 
1. copy the [`plugins/wp-factchecker-claim-review`](plugins/wp-factchecker-claim-review) folder to your site's plugin folder
2. Activate the plugin via the WordPress plugin settings
3. Under **Settings > Factchecker ClaimReview Settings** set your organisation detail, and choose the post types that will have ClaimReview schema 


### Adding ClaimReview Schema to posts

Claims are separate from posts and can be reused  - and are defined as a Custom Post Type. 

For the post types enabled above, a new metabox will appear in the right column when editing a post. 

You can search for existing ClaimReviews, or create new ones. 

Save/Publish your post, and your new ClaimReview Schema data will be added to the HTML when the post renders. 



## Contributing to the development of this plugin

This is a simple start - there is much to be done. Please contribute to make this a well rounded and useful plugin. 

To make development easier I have added a [`docker-compose` configuration](./docker-compose.yml), which will allow  you to spin up a local version of a wordpress site in Docker Containers and focus on developing the functionality of this plugin.

This is not a requirement, but I find it a useful way to work. [Read more about setting up docker in your development environment](https://www.docker.com)


### Development approach
I think it's important to have this as a zero-dependency plugin, so it can have as low a barrier to adoption as possible. I have therefore not used any 3rd-party plugins for things like custom fields or JavaScript - only what is available with a standard WordPress plugin. 



### TODO:

- [ ] Translations
- [ ] More configurable/complete Schema options
- [ ] ... others? 

Please raise any bugs or feature requests in [issues](./issues). 

## Copyright and License

This project is licensed under the [GNU GPL](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html), version 2 or later.

2017 © [Adam Davis](http://admataz.com).





 
 
 


 