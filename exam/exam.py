import pandas as pd
import mysql.connector
from mysql.connector import Error
import numpy as np

# ------------------ 配置 ------------------
db_config = {
    'host': 'localhost',
    'user': 'exam',
    'password': 'exam181818',
    'database': 'exam'
}

excel_file = 'exam.xlsx'
# -----------------------------------------

def get_sheet_map(file_path):
    """返回 {索引: 名称} 的字典，方便后续选择"""
    xls = pd.ExcelFile(file_path)
    sheets = xls.sheet_names
    return dict(enumerate(sheets, start=1)), xls


def ask_user(sheet_map):
    """交互式让用户选择要导入的 sheet"""
    print("Excel 中共有以下 sheet：")
    for idx, name in sheet_map.items():
        print(f"{idx}: {name}")

    while True:
        choice = input(
            "\n请选择要导入的 sheet：\n"
            "  0  全部导入\n"
            "  单个数字（如 2）\n"
            "  多个数字用空格或逗号分隔（如 1 3 4）\n"
            "请输入："
        ).strip()

        if choice == '0':
            return list(sheet_map.values())

        try:
            # 支持空格或逗号分隔
            idxs = [int(x) for x in choice.replace(',', ' ').split()]
            if not idxs:
                raise ValueError
            # 去掉重复并校验范围
            idxs = sorted(set(idxs))
            if any(i not in sheet_map for i in idxs):
                raise ValueError
            return [sheet_map[i] for i in idxs]
        except ValueError:
            print("输入无效，请重新输入！\n")


def read_selected_sheets(xls, selected):
    """仅读取用户选中的 sheet"""
    data = {}
    for sheet in selected:
        df = pd.read_excel(xls, sheet_name=sheet)
        data[sheet] = df
    return data


def insert_data_to_db(data):
    try:
        conn = mysql.connector.connect(**db_config)
        if conn.is_connected():
            cursor = conn.cursor()

            # 单选题
            if '单选题' in data:
                for _, row in data['单选题'].iterrows():
                    row = row.replace({np.nan: None})
                    cursor.execute("""
                        INSERT INTO single_choice
                        (difficulty, question, answer, option_a, option_b, option_c, option_d, option_e, option_f)
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """, tuple(row))

            # 多选题
            if '多选题' in data:
                for _, row in data['多选题'].iterrows():
                    row = row.replace({np.nan: None})
                    cursor.execute("""
                        INSERT INTO multiple_choice
                        (difficulty, question, answer, option_a, option_b, option_c, option_d, option_e, option_f)
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """, tuple(row))

            # 判断题
            if '判断题' in data:
                for _, row in data['判断题'].iterrows():
                    row = row.replace({np.nan: None})
                    cursor.execute("""
                        INSERT INTO true_false (difficulty, question, answer)
                        VALUES (%s, %s, %s)
                    """, tuple(row))

            # 填空题
            if '填空题' in data:
                for _, row in data['填空题'].iterrows():
                    row = row.replace({np.nan: None})
                    cursor.execute("""
                        INSERT INTO fill_in_the_blanks (difficulty, question, answer)
                        VALUES (%s, %s, %s)
                    """, tuple(row))

            conn.commit()
            print("数据插入成功！")
    except Error as e:
        print(f"数据库连接或数据插入失败：{e}")
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()


def main():
    sheet_map, xls = get_sheet_map(excel_file)
    selected_sheets = ask_user(sheet_map)
    data = read_selected_sheets(xls, selected_sheets)
    insert_data_to_db(data)


if __name__ == "__main__":
    main()