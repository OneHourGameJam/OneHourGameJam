CREATE TABLE `config` (
  `config_id` int(11) NOT NULL,
  `config_lastedited` datetime NOT NULL,
  `config_lasteditedby` varchar(45) NOT NULL,
  `config_key` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `config_value` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `config_category` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `config_description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `config_type` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `config_options` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `config_editable` tinyint(1) NOT NULL,
  `config_required` tinyint(1) NOT NULL,
  `config_template` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


--
-- Indexes for table `user`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`config_id`),
  ADD UNIQUE KEY `config_key_UNIQUE` (`config_key`);

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
