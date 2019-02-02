     SELECT SQL_CALC_FOUND_ROWS
            `hTicketId`,
            `hUserId`,
            `hTicketSubject`,
            `hTicketBody`,
            `hTicketIsOpen`,
            `hTicketSeverity`,
            `hTicketCreated`,
            `hTicketLastModified`,
            `hTicketLastModifiedBy`
       FROM `hTickets`
      WHERE {where}
   ORDER BY `hTicketCreated` {direction}
{limit?
      LIMIT {limit}
}
