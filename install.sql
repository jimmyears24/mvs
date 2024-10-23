CREATE TABLE `mvs_options` (
  `option_id` bigint NOT NULL,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `scraper_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `mvs_options`
  ADD PRIMARY KEY (`option_id`);

ALTER TABLE `mvs_options`
  MODIFY `option_id` bigint NOT NULL AUTO_INCREMENT;

CREATE TABLE `mvs_posts` (
  `id` int NOT NULL,
  `posted` timestamp NULL DEFAULT NULL,
  `post_id` int NOT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `separated` int DEFAULT NULL,
  `profile_id` int NOT NULL,
  `profile_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` float NOT NULL,
  `link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `mvs_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

ALTER TABLE `mvs_posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

CREATE TABLE `mvs_profiles` (
  `profile_id` bigint NOT NULL,
  `json` json NOT NULL,
  `done` tinyint(1) NOT NULL,
  `scraper_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `mvs_profiles`
  ADD PRIMARY KEY (`profile_id`);

ALTER TABLE `mvs_profiles`
  MODIFY `profile_id` bigint NOT NULL AUTO_INCREMENT;

CREATE TABLE `mvs_subscriptions` (
  `subscription_id` bigint NOT NULL,
  `id` bigint NOT NULL,
  `stage_name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scraper_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `mvs_subscriptions`
  MODIFY `subscription_id` bigint NOT NULL AUTO_INCREMENT;

ALTER TABLE `mvs_subscriptions`
  ADD PRIMARY KEY (`subscription_id`);
