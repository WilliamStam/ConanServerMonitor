CREATE VIEW IF NOT EXISTS "_Structures" AS


SELECT
	pb.name AS Owner,
	pb.pb_id AS pb_id,
	pb.type AS type,
	COUNT(bi.instance_id) AS 'Pieces',
	ap.x || ' ' || ap.y || ' ' || ap.z AS Location
	FROM
		building_instances AS bi
			INNER JOIN buildings b ON b.object_id = bi.object_id
			INNER JOIN actor_position ap ON ap.id = bi.object_id
			INNER JOIN (
			SELECT guildid AS pb_id, name, 'clan' AS type
				FROM guilds
			UNION
			SELECT id, char_name, 'solo' AS type
				FROM
					characters
			) pb ON b.owner_id = pb_id
	GROUP BY bi.object_id
	ORDER BY lower(Owner), COUNT(bi.instance_id) DESC;