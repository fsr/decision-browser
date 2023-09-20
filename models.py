from datetime import datetime
from typing import Optional
import re

from sqlmodel import Field, SQLModel


def replace_tex_manoy(tex_str):
    # replace e.g. \EUR{70} -> 70 Euro
    # \EUR{42} -> 42 Euro
    return re.sub(r"\\EUR\{(\d*)\}", r"\1 Euro", tex_str)


class Beschluss(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    vote_money: Optional[int] = Field(default=None, nullable=True)
    vote_number: Optional[int] = Field(default=None, nullable=True)
    vote_text: str = Field(default="", nullable=True, max_length=1000)
    vote_submitter: str = Field(default="", nullable=True, max_length=1000)
    vote_reason: str = Field(default="", nullable=True, max_length=1000)
    voting_dafuer: str = Field(default=None, nullable=True)
    voting_dagegen: str = Field(default=None, nullable=True)
    voting_enthaltung: int = Field(default=None, nullable=True)
    date: datetime

    def from_tuple(tuple, date, i, vote_number):
        if tuple[1]:
            return Beschluss(
                vote_money=tuple[0],
                vote_number=tuple[1],
                vote_text=replace_tex_manoy(tuple[2]),
                vote_submitter=tuple[3],
                vote_reason=tuple[4],
                voting_dafuer=tuple[5],
                voting_dagegen=tuple[6],
                voting_enthaltung=tuple[7],
                date=date,
            )
        else:
            return Beschluss(
                vote_money=tuple[0],
                vote_number=vote_number + i,
                vote_text=replace_tex_manoy(tuple[2]),
                vote_submitter=tuple[3],
                vote_reason=tuple[4],
                voting_dafuer=tuple[5],
                voting_dagegen=tuple[6],
                voting_enthaltung=tuple[7],
                date=date,
            )
