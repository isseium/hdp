CREATE TABLE wantToEatCategory
(
      `date`        DATE NOT NULL       -- 対象日付
    , `fb_id`       INTEGER NOT NULL    -- 夫のFBID
    , `category_id` INTEGER NOT NULL    -- 選択したカテゴリ
    , `created_at`  TIMESTAMP           -- 登録時刻
    , primary key(  `date`
                  , `fb_id`
                 )
);
