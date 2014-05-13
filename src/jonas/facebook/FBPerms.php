<?php
/**
 * FBPerms
 * From https://developers.facebook.com/docs/authentication/permissions/
 * @date 2012-20-09
 */

class FBPerms {

    //User and Friends Permissions
    const user_about_me = 'user_about_me';
    const user_activities = 'user_activities';
    const user_birthday = 'user_birthday';
    const user_checkins = 'user_checkins';
    const user_education_history = 'user_education_history';
    const user_events = 'user_events';
    const user_groups = 'user_groups';
    const user_hometown = 'user_hometown';
    const user_interests = 'user_interests';
    const user_likes = 'user_likes';
    const user_location = 'user_location';
    const user_notes = 'user_notes';
    const user_photos = 'user_photos';
    const user_questions = 'user_questions';
    const user_relationships = 'user_relationships';
    const user_relationship_details = 'user_relationship_details';
    const user_religion_politics = 'user_religion_politics';
    const user_status = 'user_status';
    const user_subscriptions = 'user_subscriptions';
    const user_videos = 'user_videos';
    const user_website = 'user_website';
    const user_work_history = 'user_work_history';
    const email = 'email';

    const friends_about_me = 'friends_about_me';
    const friends_activities = 'friends_activities';
    const friends_birthday = 'friends_birthday';
    const friends_checkins = 'friends_checkins';
    const friends_education_history = 'friends_education_history';
    const friends_events = 'friends_events';
    const friends_groups = 'friends_groups';
    const friends_hometown = 'friends_hometown';
    const friends_interests = 'friends_interests';
    const friends_likes = 'friends_likes';
    const friends_location = 'friends_location';
    const friends_notes = 'friends_notes';
    const friends_photos = 'friends_photos';
    const friends_questions = 'friends_questions';
    const friends_relationships = 'friends_relationships';
    const friends_relationship_details = 'friends_relationship_details';
    const friends_religion_politics = 'friends_religion_politics';
    const friends_status = 'friends_status';
    const friends_subscriptions = 'friends_subscriptions';
    const friends_videos = 'friends_videos';
    const friends_website = 'friends_website';
    const friends_work_history = 'friends_work_history';

    //Extended permissions
    const read_friendlists = 'read_friendlists';
    const read_insights = 'read_insights';
    const read_mailbox = 'read_mailbox';
    const read_requests = 'read_requests';
    const read_stream = 'read_stream';
    const xmpp_login = 'xmpp_login';
    const ads_management = 'ads_management';
    const create_event = 'create_event';
    const manage_friendlists = 'manage_friendlists';
    const manage_notifications = 'manage_notifications';
    const user_online_presence = 'user_online_presence';
    const friends_online_presence = 'friends_online_presence';
    const publish_checkins = 'publish_checkins';
    const publish_stream = 'publish_stream';
    const rsvp_event = 'rsvp_event';

    //Open Graph Permissions
    const publish_actions = 'publish_actions';
    const user_actions_music = 'user_actions.music';
    const user_actions_news = 'user_actions.news';
    const user_actions_video = 'user_actions.video';
    const user_games_activity = 'user_games_activity';
    const user_actions = 'user_actions:'; //user_actions:APP_NAMESPACE

    const friends_actions_music = 'friends_actions.music';
    const friends_actions_news = 'friends_actions.news';
    const friends_actions_video = 'friends_actions.video';
    const friends_games_activity = 'friends_games_activity';
    const friends_actions = 'friends_actions:'; //friends_actions:APP_NAMESPACE

    //Page permissions
    const manage_pages = 'manage_pages';
}