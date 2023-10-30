SELECT        dbo.Cust_User.*
FROM            dbo.Cust_User;

INSERT INTO [dbo].[Cust_User]
           ([ID]
           ,[EmployeeCode]
           ,[FristName]
           ,[LastName]
           ,[LoginID]
           ,[PWD]
           ,[OfficeID]
           ,[OfficeCode]
           ,[Role]
           ,[CreatedBy]
           ,[CreatedOn]
           ,[LastModifiedBy]
           ,[LastModifiedOn]
           ,[IsActive]
           ,[IsDeleted])
     VALUES
           ( CONVERT(uniqueidentifier,'DF215E10-8BD4-4401-B2DC-99BB03135F2E'), 1,
		   'Emma',
		   'Aman',
		   1234,
		   '$2y$10$92I',
           CONVERT(uniqueidentifier,'935A4BDE-0815-4CD9-9D10-49C109B0C0E3'),
           1,
           1,
           'Emmanuel',
           '2021-07-10 08:44:35',
           'Emmanuel',
           '2021-07-10 08:44:35',
           1,
           1)

 SELECT CONVERT(uniqueidentifier,'DF215E10-8BD4-4401-B2DC-99BB03135F2E') UNIQUEID

 SELECT NEWID();