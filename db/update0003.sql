-- Add the type (ie http or id)
alter table REDIRECTIONS_LOG
    rename column TYPE to METHOD;
alter table REDIRECTIONS_LOG
    add column TYPE TEXT;

-- Rename redirection to page rules
create table PAGE_RULES_tmp
(
    ID                 INTEGER PRIMARY KEY,
    MATCHER            TEXT unique, -- the matcher pattern
    TARGET             TEXT,        -- the target
    PRIORITY           INTEGER,     -- the priority in which the match must be performed
    TIMESTAMP          TIMESTAMP    -- a update/create timestamp
);

insert into PAGE_RULES_tmp(ID, MATCHER, TARGET, PRIORITY, TIMESTAMP)
select NULL, SOURCE, TARGET, ROW_NUMBER() over (), CREATION_TIMESTAMP
from REDIRECTIONS;
drop table REDIRECTIONS;
alter table PAGE_RULES_tmp
    rename to PAGE_RULES;


